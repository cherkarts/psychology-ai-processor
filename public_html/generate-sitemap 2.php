<?php
/**
 * Автоматический генератор карты сайта
 * Создает sitemap.xml для поисковых роботов
 * Поддерживает красивые ЧПУ URL
 */

require_once 'includes/functions.php';

class SitemapGenerator
{
  private $baseUrl = 'https://cherkas-therapy.ru';
  private $sitemapFile = 'sitemap.xml';
  private $pages = [];
  private $articles = [];
  private $products = [];
  private $categories = [];

  public function __construct()
  {
    $this->scanPages();
    $this->scanArticles();
    $this->scanProducts();
    $this->generateSitemap();
  }

  /**
   * Сканирует основные страницы сайта с ЧПУ
   */
  private function scanPages()
  {
    $this->pages = [
      [
        'url' => '/',
        'priority' => '1.0',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/uslugi',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/o-nas',
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/otzyvy',
        'priority' => '0.8',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/ceny',
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/meditatsii',
        'priority' => '0.7',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/magazin',
        'priority' => '0.7',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/kontakty',
        'priority' => '0.6',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      // Новые страницы специализаций с ЧПУ
      [
        'url' => '/rabota-s-zavisimostyami',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/sozavisimost',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/trevozhnost-i-strahi',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ],
      [
        'url' => '/slozhnosti-v-otnosheniyah',
        'priority' => '0.9',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
      ]
    ];
  }

  /**
   * Сканирует статьи из базы данных с ЧПУ
   */
  private function scanArticles()
  {
    try {
      $db = getDB();
      $stmt = $db->query("SELECT slug, updated_at FROM articles WHERE status = 'published' ORDER BY updated_at DESC");

      while ($row = $stmt->fetch()) {
        $this->articles[] = [
          'url' => '/statya/' . $row['slug'],
          'priority' => '0.7',
          'changefreq' => 'monthly',
          'lastmod' => date('Y-m-d', strtotime($row['updated_at']))
        ];
      }
    } catch (Exception $e) {
      error_log("Ошибка при сканировании статей: " . $e->getMessage());
    }
  }

  /**
   * Сканирует товары из базы данных с ЧПУ
   */
  private function scanProducts()
  {
    try {
      $db = getDB();
      $stmt = $db->query("SELECT slug, updated_at FROM products WHERE status = 'active' ORDER BY updated_at DESC");

      while ($row = $stmt->fetch()) {
        $this->products[] = [
          'url' => '/tovar/' . $row['slug'],
          'priority' => '0.6',
          'changefreq' => 'weekly',
          'lastmod' => date('Y-m-d', strtotime($row['updated_at']))
        ];
      }
    } catch (Exception $e) {
      error_log("Ошибка при сканировании товаров: " . $e->getMessage());
    }
  }

  /**
   * Генерирует XML карту сайта
   */
  private function generateSitemap()
  {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // Добавляем основные страницы
    foreach ($this->pages as $page) {
      $xml .= $this->generateUrlTag($page);
    }

    // Добавляем статьи
    foreach ($this->articles as $article) {
      $xml .= $this->generateUrlTag($article);
    }

    // Добавляем товары
    foreach ($this->products as $product) {
      $xml .= $this->generateUrlTag($product);
    }

    $xml .= '</urlset>';

    // Сохраняем файл
    if (file_put_contents($this->sitemapFile, $xml)) {
      echo "Карта сайта успешно создана: {$this->sitemapFile}\n";
      echo "Всего URL: " . (count($this->pages) + count($this->articles) + count($this->products)) . "\n";
      echo "ЧПУ URL настроены для SEO оптимизации\n";
    } else {
      echo "Ошибка при создании карты сайта\n";
    }
  }

  /**
   * Генерирует XML тег для одного URL
   */
  private function generateUrlTag($item)
  {
    $xml = "  <url>\n";
    $xml .= "    <loc>" . $this->baseUrl . $item['url'] . "</loc>\n";
    $xml .= "    <lastmod>" . $item['lastmod'] . "</lastmod>\n";
    $xml .= "    <changefreq>" . $item['changefreq'] . "</changefreq>\n";
    $xml .= "    <priority>" . $item['priority'] . "</priority>\n";
    $xml .= "  </url>\n";
    return $xml;
  }

  /**
   * Создает robots.txt файл
   */
  public function generateRobotsTxt()
  {
    $robots = "User-agent: *\n";
    $robots .= "Allow: /\n\n";
    $robots .= "Disallow: /admin/\n";
    $robots .= "Disallow: /includes/\n";
    $robots .= "Disallow: /logs/\n";
    $robots .= "Disallow: /vendor/\n\n";
    $robots .= "Sitemap: " . $this->baseUrl . "/sitemap.xml\n";
    $robots .= "Host: " . $this->baseUrl . "\n";

    if (file_put_contents('robots.txt', $robots)) {
      echo "robots.txt успешно создан\n";
    } else {
      echo "Ошибка при создании robots.txt\n";
    }
  }
}

// Запускаем генерацию
echo "Начинаю генерацию карты сайта с ЧПУ...\n";
$generator = new SitemapGenerator();
$generator->generateRobotsTxt();
echo "Готово!\n";
?>