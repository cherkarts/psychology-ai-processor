<?php
/**
 * Менеджер медитаций через базу данных
 * Автоматический расчет времени, обновление статистики категорий
 */

require_once __DIR__ . '/Database.php';

class MeditationsManager
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Автоматический расчет длительности аудио файла
     */
    public function calculateAudioDuration($audioPath)
    {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $audioPath;

        if (!file_exists($fullPath)) {
            return 0;
        }

        // Используем getID3 для получения информации об аудио файле
        if (function_exists('getid3')) {
            $getID3 = new getID3;
            $fileInfo = $getID3->analyze($fullPath);

            if (isset($fileInfo['playtime_seconds'])) {
                return round($fileInfo['playtime_seconds']);
            }
        }

        // Альтернативный способ через ffprobe (если установлен)
        $output = shell_exec("ffprobe -v quiet -show_entries format=duration -of csv=p=0 " . escapeshellarg($fullPath));
        if ($output) {
            return round((float) trim($output));
        }

        return 0;
    }

    /**
     * Форматирование времени в минуты и секунды
     */
    public function formatDuration($seconds)
    {
        if ($seconds == 0)
            return '0 мин';

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes == 0) {
            return $remainingSeconds . ' сек';
        } elseif ($remainingSeconds == 0) {
            return $minutes . ' мин';
        } else {
            return $minutes . ' мин ' . $remainingSeconds . ' сек';
        }
    }

    /**
     * Обновление статистики категорий
     */
    public function updateCategoryStats()
    {
        $sql = "UPDATE meditation_categories mc 
                SET meditation_count = (
                    SELECT COUNT(*) 
                    FROM meditations m 
                    WHERE m.category_id = mc.id
                )";

        return $this->db->query($sql);
    }

    /**
     * Добавление новой медитации
     */
    public function addMeditation($meditationData)
    {
        // Автоматический расчет длительности
        if (!empty($meditationData['audio_file'])) {
            $duration = $this->calculateAudioDuration($meditationData['audio_file']);
            $meditationData['duration'] = $duration;
        } else {
            $meditationData['duration'] = 0;
        }

        // Добавление метаданных
        $meditationData['created_at'] = date('Y-m-d H:i:s');
        $meditationData['updated_at'] = date('Y-m-d H:i:s');
        $meditationData['likes'] = $meditationData['likes'] ?? 0;
        $meditationData['favorites'] = $meditationData['favorites'] ?? 0;

        // Проверка существования категории
        if (!empty($meditationData['category_id'])) {
            $categoryExists = $this->db->fetchOne(
                "SELECT id FROM meditation_categories WHERE id = ?",
                [$meditationData['category_id']]
            );

            if (!$categoryExists) {
                throw new Exception("Категория с ID {$meditationData['category_id']} не найдена");
            }
        }

        $meditationId = $this->db->insert('meditations', $meditationData);

        // Обновляем статистику категорий
        $this->updateCategoryStats();

        // Логируем действие
        $this->db->logActivity('meditation_created', 'meditation', $meditationId);

        return $meditationId;
    }

    /**
     * Добавление новой категории
     */
    public function addCategory($categoryData)
    {
        $categoryData['created_at'] = date('Y-m-d H:i:s');
        $categoryData['updated_at'] = date('Y-m-d H:i:s');
        $categoryData['is_active'] = $categoryData['is_active'] ?? 1;
        $categoryData['sort_order'] = $categoryData['sort_order'] ?? 0;

        $categoryId = $this->db->insert('meditation_categories', $categoryData);

        // Логируем действие
        $this->db->logActivity('meditation_category_created', 'meditation_category', $categoryId);

        return $categoryId;
    }

    /**
     * Получение всех медитаций
     */
    public function getAllMeditations($filters = [])
    {
        $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE 1=1";

        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND m.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['is_free'])) {
            $sql .= " AND m.is_free = ?";
            $params[] = $filters['is_free'] ? 1 : 0;
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (m.title LIKE ? OR m.description LIKE ? OR m.subtitle LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY m.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = $filters['limit'];
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получение всех категорий
     */
    public function getAllCategories()
    {
        $sql = "SELECT mc.*, COUNT(m.id) as meditation_count 
                FROM meditation_categories mc 
                LEFT JOIN meditations m ON mc.id = m.category_id 
                WHERE mc.is_active = 1 
                GROUP BY mc.id 
                ORDER BY mc.sort_order, mc.name";

        return $this->db->fetchAll($sql);
    }

    /**
     * Получение медитации по ID
     */
    public function getMeditationById($id)
    {
        $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.id = ?";

        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Получение медитации по slug
     */
    public function getMeditationBySlug($slug)
    {
        $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.slug = ?";

        return $this->db->fetchOne($sql, [$slug]);
    }

    /**
     * Получение медитаций по категории
     */
    public function getMeditationsByCategory($categoryId, $limit = null)
    {
        $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.category_id = ? 
                ORDER BY m.created_at DESC";

        $params = [$categoryId];

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Обновление медитации
     */
    public function updateMeditation($id, $meditationData)
    {
        // Автоматический расчет длительности если изменился аудио файл
        if (!empty($meditationData['audio_file'])) {
            $currentMeditation = $this->getMeditationById($id);
            if ($currentMeditation && $meditationData['audio_file'] !== $currentMeditation['audio_file']) {
                $duration = $this->calculateAudioDuration($meditationData['audio_file']);
                $meditationData['duration'] = $duration;
            }
        }

        $meditationData['updated_at'] = date('Y-m-d H:i:s');

        $result = $this->db->update('meditations', $meditationData, 'id = ?', [$id]);

        if ($result) {
            // Обновляем статистику категорий
            $this->updateCategoryStats();

            // Логируем действие
            $this->db->logActivity('meditation_updated', 'meditation', $id);
        }

        return $result;
    }

    /**
     * Удаление медитации
     */
    public function deleteMeditation($id)
    {
        $result = $this->db->delete('meditations', 'id = ?', [$id]);

        if ($result) {
            // Обновляем статистику категорий
            $this->updateCategoryStats();

            // Логируем действие
            $this->db->logActivity('meditation_deleted', 'meditation', $id);
        }

        return $result;
    }

    /**
     * Получение статистики
     */
    public function getStats()
    {
        $sql = "SELECT 
                    COUNT(*) as total_meditations,
                    SUM(duration) as total_duration,
                    COUNT(DISTINCT category_id) as total_categories
                FROM meditations";

        $stats = $this->db->fetchOne($sql);

        return [
            'total_meditations' => $stats['total_meditations'] ?? 0,
            'total_categories' => $stats['total_categories'] ?? 0,
            'total_duration' => $stats['total_duration'] ?? 0,
            'total_duration_formatted' => $this->formatDuration($stats['total_duration'] ?? 0)
        ];
    }

    /**
     * Обновление лайков и избранного
     */
    public function updateMeditationStats($id, $type, $value)
    {
        $data = [];

        if ($type === 'likes') {
            $data['likes'] = $value;
        } elseif ($type === 'favorites') {
            $data['favorites'] = $value;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('meditations', $data, 'id = ?', [$id]);
    }

    /**
     * Получение настроек
     */
    public function getSettings()
    {
        $settings = $this->db->fetchAll("SELECT * FROM site_settings WHERE setting_key LIKE 'meditation_%'");

        $result = [];
        foreach ($settings as $setting) {
            $key = str_replace('meditation_', '', $setting['setting_key']);
            $result[$key] = $setting['setting_value'];
        }

        return $result;
    }

    /**
     * Обновление настроек
     */
    public function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            $settingKey = 'meditation_' . $key;

            $existing = $this->db->fetchOne(
                "SELECT id FROM site_settings WHERE setting_key = ?",
                [$settingKey]
            );

            if ($existing) {
                $this->db->update(
                    'site_settings',
                    ['setting_value' => $value],
                    'setting_key = ?',
                    [$settingKey]
                );
            } else {
                $this->db->insert('site_settings', [
                    'setting_key' => $settingKey,
                    'setting_value' => $value,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        return true;
    }

    /**
     * Создание slug из названия
     */
    public function slugify($text)
    {
        // Транслитерация кириллицы
        $converter = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ь' => '',
            'ы' => 'y',
            'ъ' => '',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',

            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ь' => '',
            'Ы' => 'Y',
            'Ъ' => '',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya'
        );

        $text = strtr($text, $converter);
        $text = strtolower($text);
        $text = preg_replace('/[^-a-z0-9_]+/', '-', $text);
        $text = trim($text, '-');

        return $text;
    }
}

/**
 * Функция для создания шаблона новой медитации
 */
function createMeditationTemplate($title, $subtitle, $category, $description, $audioFile = null, $metaDescription = null)
{
    $manager = new MeditationsManager();

    $meditationData = [
        'title' => $title,
        'subtitle' => $subtitle,
        'category_id' => $category,
        'description' => $description,
        'audio_file' => $audioFile ?: '/audio/meditations/' . $manager->slugify($title) . '.mp3',
        'meta_description' => $metaDescription ?: $description
    ];

    return $manager->addMeditation($meditationData);
}

/**
 * Функция для получения отформатированной длительности
 */
function getFormattedDuration($seconds)
{
    $manager = new MeditationsManager();
    return $manager->formatDuration($seconds);
}
?>