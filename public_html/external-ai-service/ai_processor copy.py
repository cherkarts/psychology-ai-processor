#!/usr/bin/env python3
"""
Внешний сервис для обработки задач генерации статей с ИИ
Этот скрипт должен работать на сервере с доступом к OpenAI API через VPN
"""

import requests
import json
import time
import logging
import os
from datetime import datetime
from typing import Dict, List, Optional
from openai import OpenAI
from dotenv import load_dotenv
from PIL import Image
import io
import base64

# Загрузка переменных окружения из .env (если файл есть)
load_dotenv()

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('ai_processor.log'),
        logging.StreamHandler()
    ]
)

class AITaskProcessor:
    def __init__(self, api_url: str, api_key: str = None):
        self.api_url = api_url
        self.api_key = api_key
        self.openai_client = None
        
        # Инициализация OpenAI клиента
        if os.getenv('OPENAI_API_KEY'):
            # Настройка прокси если указан и таймаутов клиента
            proxy_url = os.getenv('OPENAI_PROXY_URL')
            # Увеличенный таймаут для больших статей (по умолчанию 300 сек = 5 минут)
            request_timeout = int(os.getenv('OPENAI_TIMEOUT', '300'))
            import httpx
            # httpx 0.28+: параметр proxies не поддерживается — используем env-переменные
            if proxy_url:
                os.environ['HTTPS_PROXY'] = proxy_url
                os.environ['HTTP_PROXY'] = proxy_url
            http_client = httpx.Client(timeout=request_timeout)
            self.openai_client = OpenAI(
                api_key=os.getenv('OPENAI_API_KEY'),
                http_client=http_client
            )
        
        # Настройки
        self.check_interval = 30  # секунды между проверками новых задач
        self.max_retries = 3
        self.timeout = 300  # таймаут для запросов
    
    # Приведение значений из БД/HTTP к булевому типу
    def _to_bool(self, value, default: bool = False) -> bool:
        try:
            if value is None:
                return default
            if isinstance(value, bool):
                return value
            if isinstance(value, (int, float)):
                return value != 0
            s = str(value).strip().lower()
            if s in ("1", "true", "yes", "y", "on"):  # истино-подобные
                return True
            if s in ("0", "false", "no", "n", "off", ""):  # ложно-подобные
                return False
            return default
        except Exception:
            return default
        
    def get_pending_tasks(self) -> List[Dict]:
        """Получить список задач для обработки"""
        for attempt in range(3):
            try:
                headers = {
                    'User-Agent': 'AI-Worker/1.0 (GitHub Actions)',
                    'Accept': 'application/json',
                    'Connection': 'keep-alive'
                }
                response = requests.get(
                    f"{self.api_url}/ai-generation.php", 
                    headers=headers,
                    timeout=30,
                    verify=True
                )
                if response.status_code == 200:
                    data = response.json()
                    if data.get('success'):
                        return data.get('tasks', [])
                return []
            except Exception as e:
                logging.error(f"Попытка {attempt + 1}/3: Ошибка при получении задач: {e}")
                if attempt < 2:
                    time.sleep(5)  # Ждем 5 секунд перед повтором
                else:
                    return []
    
    def update_task_status(self, task_id: str, status: str, additional_data: Dict = None) -> bool:
        """Обновить статус задачи"""
        try:
            headers = {
                'User-Agent': 'AI-Worker/1.0 (GitHub Actions)',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
            payload = {
                'action': 'update_status',
                'task_id': task_id,
                'status': status
            }
            if additional_data:
                payload['additional_data'] = additional_data
                
            # Попытка 1: JSON
            response = requests.post(
                f"{self.api_url}/ai-generation.php",
                json=payload,
                headers=headers,
                timeout=30
            )
            logging.info(f"Update status (json) response: {response.status_code} - {response.text[:300]}")
            ok = False
            try:
                ok = response.status_code == 200 and response.json().get('success', False)
            except Exception:
                ok = False
            if ok:
                return True

            # Попытка 2: form-urlencoded
            import json as _json
            form_headers = {
                'User-Agent': 'AI-Worker/1.0 (GitHub Actions)',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded'
            }
            form_payload = {
                'action': 'update_status',
                'task_id': task_id,
                'status': status,
            }
            if additional_data:
                form_payload['additional_data'] = _json.dumps(additional_data, ensure_ascii=False)
            response2 = requests.post(
                f"{self.api_url}/ai-generation.php",
                data=form_payload,
                headers=form_headers,
                timeout=30
            )
            logging.info(f"Update status (form) response: {response2.status_code} - {response2.text[:300]}")
            try:
                return response2.status_code == 200 and response2.json().get('success', False)
            except Exception:
                return False
        except Exception as e:
            logging.error(f"Ошибка при обновлении статуса задачи {task_id}: {e}")
            return False
    
    def complete_task(self, task_id: str, result: Dict, task: Dict = None) -> bool:
        """Завершить задачу с результатом"""
        try:
            # Validate result data
            if not result or not isinstance(result, dict):
                logging.error(f"Invalid result data for task {task_id}: {result}")
                return False
            
            # Ensure we have required fields
            if not result.get('content'):
                logging.error(f"Missing content in result for task {task_id}")
                return False
            
            headers = {
                'User-Agent': 'AI-Worker/1.0 (GitHub Actions)',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
            payload = {
                'action': 'complete_task',
                'task_id': task_id,
                'result': result
            }
            
            # Попытка 1: JSON
            response = requests.post(
                f"{self.api_url}/ai-generation.php",
                json=payload,
                headers=headers,
                timeout=30
            )
            logging.info(f"Complete task (json) response: {response.status_code} - {response.text[:300]}")
            ok = False
            try:
                ok = response.status_code == 200 and response.json().get('success', False)
            except Exception as e:
                logging.error(f"Error parsing JSON response: {e}")
                ok = False
            if ok:
                return True

            # Попытка 2: form-urlencoded
            import json as _json
            form_headers = {
                'User-Agent': 'AI-Worker/1.0 (GitHub Actions)',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded'
            }
            # Get title from result or task
            title = result.get('meta_title', '') or (task or {}).get('title', '') or (task or {}).get('topic', '')
            if not title:
                logging.error(f"No title found for task {task_id}")
                return False
                
            form_payload = {
                'action': 'complete_task',
                'task_id': task_id,
                'title': title,
                'result': _json.dumps(result, ensure_ascii=False)
            }
            response2 = requests.post(
                f"{self.api_url}/ai-generation.php",
                data=form_payload,
                headers=form_headers,
                timeout=30
            )
            logging.info(f"Complete task (form) response: {response2.status_code} - {response2.text[:300]}")
            try:
                return response2.status_code == 200 and response2.json().get('success', False)
            except Exception as e:
                logging.error(f"Error parsing form response: {e}")
                return False
        except Exception as e:
            logging.error(f"Ошибка при завершении задачи {task_id}: {e}")
            return False
    
    def fail_task(self, task_id: str, error_message: str) -> bool:
        """Пометить задачу как неудачную"""
        try:
            payload = {
                'action': 'fail_task',
                'task_id': task_id,
                'error': error_message
            }
            
            response = requests.post(
                f"{self.api_url}/ai-generation.php",
                json=payload,
                timeout=10
            )
            return response.status_code == 200 and response.json().get('success', False)
        except Exception as e:
            logging.error(f"Ошибка при пометке задачи {task_id} как неудачной: {e}")
            return False
    
    def generate_article_content(self, task: Dict) -> Dict:
        """Генерировать контент статьи с помощью OpenAI"""
        if not self.openai_client:
            raise Exception("OpenAI клиент не инициализирован")
        
        # Формируем промпт для генерации
        prompt = self.build_article_prompt(task)
        
        try:
            # Генерируем основной контент с ретраями и фолбэком модели
            logging.info("Отправляем запрос к OpenAI API...")
            response = None
            last_err = None
            for attempt in range(2):
                try:
                    # Улучшенные параметры для более креативного и эмоционального текста
                    response = self.openai_client.chat.completions.create(
                        model="gpt-4",
                        messages=[
                            {"role": "system", "content": "Ты опытный психолог и писатель. Создавай качественные, информативные статьи по психологии."},
                            {"role": "user", "content": prompt}
                        ],
                        max_tokens=4000,
                        temperature=0.9,  # Повышенная температура для большей креативности
                        top_p=0.95  # Использование top_p для дополнительной вариативности
                    )
                    break
                except Exception as e:
                    last_err = e
                    logging.error(f"OpenAI attempt {attempt+1} failed: {e}")
                    time.sleep(2)
            if response is None:
                # Фолбэк на более лёгкую модель
                logging.info("Пробую фолбэк модель gpt-3.5-turbo...")
                response = self.openai_client.chat.completions.create(
                    model="gpt-3.5-turbo",
                    messages=[
                        {"role": "system", "content": "Ты опытный психолог и писатель. Создавай качественные, информативные статьи по психологии."},
                        {"role": "user", "content": prompt}
                    ],
                    max_tokens=3500,
                    temperature=0.9,
                    top_p=0.95
                )
            logging.info("Получен ответ от OpenAI API")
            
            if not response.choices or not response.choices[0].message.content:
                raise Exception("OpenAI API returned empty response")
                
            content = response.choices[0].message.content
            logging.info(f"OpenAI response length: {len(content)} characters")
            logging.info(f"OpenAI response preview: {content[:200]}...")
            
            # Извлекаем заголовок и контент
            title, main_content = self.extract_title_and_content(content)

            # Санитарная обработка контента (убираем дубли заголовков/абзацев)
            main_content = self._clean_article_html(main_content, title)
            
            # Генерируем мета-описание
            meta_description = self.generate_meta_description(title, main_content)
            
            # Ищем изображение
            featured_image = self.search_featured_image(task)
            
            # Ensure we have all required fields
            if not title or not main_content:
                raise Exception("Failed to generate title or content")
            
            return {
                'content': main_content,
                'excerpt': self.generate_excerpt(main_content),
                'meta_title': title,
                'meta_description': meta_description,
                'featured_image': featured_image
            }
            
        except Exception as e:
            logging.error(f"Ошибка при генерации контента: {e}")
            if "timeout" in str(e).lower():
                logging.error("Таймаут при обращении к OpenAI API")
            raise
    
    def build_article_prompt(self, task: Dict) -> str:
        """Строить промпт для генерации статьи"""
        keywords = json.loads(task.get('keywords', '[]')) if task.get('keywords') else []
        keywords_str = ', '.join(keywords) if keywords else 'психология, саморазвитие'

        include_faq = self._to_bool(task.get('include_faq'), False)
        include_quotes = self._to_bool(task.get('include_quotes'), True)
        include_internal_links = self._to_bool(task.get('include_internal_links'), True)
        include_toc = self._to_bool(task.get('include_table_of_contents'), True)
        seo_optimization = self._to_bool(task.get('seo_optimization'), True)

        try:
            word_count = int(task.get('word_count', 2000) or 2000)
        except Exception:
            word_count = 2000
        word_count = max(500, min(5000, word_count))
        tone = task.get('tone', 'professional')
        target_audience = task.get('target_audience', 'Общая аудитория')

        # Выбор стиля статьи в зависимости от параметра tone
        style_requirements = ""
        if tone == 'emotional':
            style_requirements = """
✨ ТРЕБОВАНИЯ К ЭМОЦИОНАЛЬНОМУ СТИЛЮ:
- Начинай статью с сильного эмоционального хука, который вызывает сопереживание
- Используй живые истории и диалоги, чтобы читатель мог себя узнать
- Пиши короткими, энергичными абзацами (до 4 строк)
- Добавляй неожиданные факты и парадоксы в каждом разделе
- Используй метафоры, сравнения и провокационные формулировки
- Чередуй серьёзные и лёгкие моменты для удержания внимания
- Говори живо, как с человеком, без канцелярской волокиты
"""
        elif tone == 'provocative':
            style_requirements = """
⚡ ТРЕБОВАНИЯ К ПРОВОКАЦИОННОМУ СТИЛЮ:
- Начинай с шокирующего факта или контринтуитивного утверждения
- Используй смелые, дерзкие формулировки, которые заставят задуматься
- Добавляй раздел "То, о чём обычно молчат" с неудобными истинами
- Пиши короткими, резкими абзацами (до 3 строк)
- Используй парадоксы, противоречия и неожиданные повороты
- Применяй метафоры и сравнения, которые вызывают сильные образы
- Избегай шаблонных фраз и клише любой ценой
"""
        else:  # professional/default
            style_requirements = """
🔬 ТРЕБОВАНИЯ К ПРОФЕССИОНАЛЬНОМУ СТИЛЮ:
- Начинай статью с интересного факта или исследования
- Используй примеры и кейсы из практики
- Пиши четкими абзацами (до 5 строк)
- Добавляй научные данные и ссылки на исследования
- Объясняй сложные концепции простым языком
- Используй метафоры и аналогии для лучшего понимания
- Избегай канцелярских формулировок и шаблонов
"""

        # Рассчитываем минимальные объемы разделов для достижения общей длины статьи
        # Общая длина = Введение + Основная часть + Практика + FAQ + Провокация + Заключение
        base_length = word_count
        intro_length = max(400, base_length // 6)  # Введение
        main_length = max(500, base_length // 4)   # Что на самом деле происходит
        importance_length = max(400, base_length // 6)  # Почему это важно
        signs_length = max(350, base_length // 7)  # Неочевидные признаки
        techniques_length = max(500, base_length // 4)  # Практические техники
        faq_length = max(300, base_length // 8) if include_faq else 0  # Частые вопросы
        provocation_length = max(250, base_length // 10)  # То, о чём обычно молчат
        conclusion_length = max(200, base_length // 12)  # Заключение
        
        prompt = f"""
Ты эксперт-психолог. Создай ГЛУБОКУЮ и ПОДРОБНУЮ статью на тему "{task['topic']}" (МИНИМУМ {word_count} СЛОВ) с неожиданными инсайтами и научными фактами.

🎯 ФОРМАТ: только HTML, начни с <h1>, закончи призывом к действию. БЕЗ служебных блоков в конце!

📊 Параметры: {keywords_str} | {target_audience} | {tone}
Опции: FAQ={include_faq}, Цитаты={include_quotes}, TOC={include_toc}, SEO={seo_optimization}

{style_requirements}

⚡ То, о чём обычно молчат:
- 3–4 мысли, которые обычно неудобно обсуживать
- Дай смелые формулировки

💎 КОНТЕНТ (главное!):
• НЕТРИВИАЛЬНЫЕ факты: неожиданные исследования, парадоксы, малоизвестные явления
• КОНКРЕТИКА: реальные кейсы из практики с деталями (имена изменены)
• ГЛУБИНА: объясняй механизмы "как это работает на уровне психики/мозга"
• УНИКАЛЬНОСТЬ: избегай общих мест, давай свежий взгляд
• ПРИМЕРЫ: 3-5 живых историй, диалоги, ситуации из жизни

📖 Структура:
<h1>Цепляющий заголовок</h1>
<p>Hook: интригующий вопрос/факт/история (3-4 предложения)</p>
{'<div class="toc"><h2>Содержание</h2><ul><li>пункты</li></ul></div>' if include_toc else ''}
<h2>Введение с неожиданным фактом</h2> (МИНИМУМ {intro_length} слов: представь удивительную статистику или исследование)
<h2>Что на самом деле происходит</h2> (МИНИМУМ {main_length} слов: детальные механизмы, исследования, 4-5 абзацев)
<h2>Почему это важно</h2> (МИНИМУМ {importance_length} слов: последствия, влияние, 3-4 абзаца + кейс)
<h2>Неочевидные признаки</h2> (МИНИМУМ {signs_length} слов: то, что люди не замечают, 4-5 пунктов)
<h2>Практические техники</h2> (МИНИМУМ {techniques_length} слов: 2-3 техники: название, когда применять, шаги, почему работает, пример)
{'<h2>Частые вопросы</h2> (МИНИМУМ ' + str(faq_length) + ' слов: 4-5 вопросов с развёрнутыми ответами)' if include_faq else ''}
<h2>⚡ То, о чём обычно молчат</h2> (МИНИМУМ {provocation_length} слов: 3-4 провокационных мысли)
<h2>Заключение</h2> (МИНИМУМ {conclusion_length} слов: краткая суть + призыв)

🔬 Требования к качеству:
✓ Текст должен вызывать сильные эмоции (удивление, шок, сопереживание)
✓ В начале — крючок, в середине — неожиданный поворот, в конце — запоминающийся вывод
✓ Каждый раздел должен содержать историю или мини-сценку, чтобы читатель мог себя узнать в тексте
✓ Излагай смело, не как Википедия. Используй парадоксы, метафоры, неожиданные сравнения
✓ Абзацы не длиннее 4–5 строк, избегай "простыней" текста
✓ НЕ используй клише, общие места, очевидные вещи
✓ НЕ пиши "в современном мире", "как известно", "не секрет"
✓ Строго соблюдай минимальные объемы разделов для достижения общего объема статьи МИНИМУМ {word_count} СЛОВ

HTML: h1, h2, h3, p, ul, li, ol, blockquote, strong, em
"""
        return prompt
    
    def extract_title_and_content(self, content: str) -> tuple:
        """Извлечь заголовок и контент из сгенерированного текста"""
        import re
        
        # Убираем лишние пробелы
        content = content.strip()
        
        # Ищем заголовок H1
        h1_match = re.search(r'<h1[^>]*>(.*?)</h1>', content, re.IGNORECASE | re.DOTALL)
        if h1_match:
            title = h1_match.group(1).strip()
            # Убираем HTML теги из заголовка
            title = re.sub(r'<[^>]+>', '', title).strip()
        else:
            # Если нет H1, ищем первый непустой текст (не вводные слова)
            lines = content.split('\n')
            title = ""
            skip_phrases = [
                "здравствуйте", "к сожалению", "я не могу", "но я могу", 
                "предложить", "в обычном формате", "вот статья", "статья на тему"
            ]
            
            for line in lines:
                line = line.strip()
                if line and len(line) > 10:  # Минимум 10 символов
                    # Убираем HTML теги
                    clean_line = re.sub(r'<[^>]+>', '', line).strip()
                    # Проверяем, не содержит ли вводные фразы
                    if not any(phrase in clean_line.lower() for phrase in skip_phrases):
                        title = clean_line
                        break
            
            # Если все еще нет заголовка, берем первую строку
            if not title:
                clean_content = re.sub(r'<[^>]+>', '', content)
                first_line = clean_content.split('\n')[0].strip()
                if first_line and len(first_line) > 5:
                    title = first_line
        
        # Если заголовок слишком длинный, обрезаем
        if len(title) > 100:
            title = title[:100].rsplit(' ', 1)[0] + '...'
        
        # Весь контент как основной контент
        main_content = content
        
        logging.info(f"Extracted title: '{title}' (length: {len(title)})")
        logging.info(f"Main content length: {len(main_content)}")
        
        return title, main_content

    def _clean_article_html(self, html: str, title: str) -> str:
        """Удалить повторные заголовки/абзацы и подряд идущие дубли."""
        try:
            import re
            cleaned = html
            
            # КРИТИЧНО: Удаляем служебные блоки "Разделы статьи", "Разделы, включенные в текст" и подобные
            # Паттерны для удаления:
            service_patterns = [
                r'<p>\s*\*\*Разделы[^<]*?:\*\*[^<]*?</p>',  # **Разделы статьи:** ...
                r'<p>\s*Разделы[^<]*?:[^<]*?</p>',  # Разделы статьи: ...
                r'<p>\s*\*\*Разделы[^<]*?включ[^<]*?:\*\*[^<]*?</p>',  # **Разделы, включенные в текст:** ...
                r'<p>\s*Разделы[^<]*?включ[^<]*?:[^<]*?</p>',  # Разделы, включенные в текст: ...
                r'<p>\s*\*\*Структура[^<]*?:\*\*[^<]*?</p>',  # **Структура статьи:** ...
                r'<p>\s*Структура[^<]*?:[^<]*?</p>',  # Структура статьи: ...
                r'<p>\s*\*\*В\s+тексте[^<]*?:\*\*[^<]*?</p>',  # **В тексте представлены:** ...
                r'<p>\s*В\s+тексте[^<]*?:[^<]*?</p>',  # В тексте представлены: ...
            ]
            
            for pattern in service_patterns:
                cleaned = re.sub(pattern, '', cleaned, flags=re.IGNORECASE | re.DOTALL)
            
            # Убираем дублирующееся повторение первого параграфа/предложения 2-3 раза подряд
            # Схема: <p>Текст...</p> сразу повторен — оставляем один
            cleaned = re.sub(r'(</p>\s*<p>\s*){2,}', '</p><p>', cleaned)

            # Убираем повтор заголовка в тексте, если он вставлен как H2/H3
            if title:
                t = re.escape(title.strip())
                cleaned = re.sub(rf'<h[23][^>]*>\s*{t}\s*</h[23]>', '', cleaned, flags=re.IGNORECASE)

            # Удаляем подряд идущие одинаковые строки (простая защита от дублей)
            lines = re.split(r'(<[^>]+>|\n)', cleaned)
            out = []
            last = None
            for part in lines:
                if part.strip() == last:
                    continue
                out.append(part)
                last = part.strip()
            cleaned = ''.join(out)
            
            # Финальная очистка: убираем пустые параграфы
            cleaned = re.sub(r'<p>\s*</p>', '', cleaned)
            
            return cleaned
        except Exception:
            return html
    
    def generate_meta_description(self, title: str, content: str) -> str:
        """Генерировать мета-описание"""
        if not self.openai_client:
            return title[:160]
        
        try:
            response = self.openai_client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": "Создавай краткие мета-описания для SEO (до 160 символов)"},
                    {"role": "user", "content": f"Создай мета-описание для статьи '{title}'. Контент: {content[:500]}..."}
                ],
                max_tokens=100,
                temperature=0.3
            )
            
            return response.choices[0].message.content.strip()[:160]
        except:
            return title[:160]
    
    def generate_excerpt(self, content: str) -> str:
        """Генерировать краткое описание"""
        # Убираем HTML теги и берем первые 200 символов
        import re
        text = re.sub('<[^<]+?>', '', content)
        return text[:200] + ('...' if len(text) > 200 else '')
    
    def search_featured_image(self, task: Dict) -> str:
        """Поиск изображения для статьи"""
        try:
            # Используем Unsplash API для поиска изображений
            unsplash_access_key = os.getenv('UNSPLASH_ACCESS_KEY')
            if not unsplash_access_key:
                return ""
            
            # Формируем поисковый запрос
            search_query = task['topic'].split()[0]  # Первое слово из темы
            keywords = json.loads(task.get('keywords', '[]')) if task.get('keywords') else []
            if keywords:
                search_query = keywords[0]
            
            url = f"https://api.unsplash.com/search/photos"
            # Чтобы уменьшить повторяемость, сдвигаем страницу выдачи по времени
            page = (int(time.time() // 3600) % 18) + 1  # цикл 1..18 каждый час
            params = {
                'query': f"{search_query} psychology",
                'per_page': 1,
                'page': page,
                'orientation': 'landscape'
            }
            headers = {
                'Authorization': f'Client-ID {unsplash_access_key}'
            }
            
            response = requests.get(url, params=params, headers=headers, timeout=10)
            if response.status_code == 200:
                data = response.json()
                if data['results']:
                    return data['results'][0]['urls']['regular']
            
            return ""
        except Exception as e:
            logging.error(f"Ошибка при поиске изображения: {e}")
            return ""
    
    def process_task(self, task: Dict) -> bool:
        """Обработать одну задачу"""
        task_id = task['task_id']
        logging.info(f"Начинаю обработку задачи {task_id}: {task['title']}")
        
        try:
            # Переводим задачу в статус processing, чтобы избежать повторной обработки
            if not self.update_task_status(task_id, 'processing', {'started_at': datetime.utcnow().isoformat()}):
                logging.warning(f"Не удалось перевести задачу {task_id} в статус processing. Пропускаем.")
                return False
            
            # Генерируем контент
            result = self.generate_article_content(task)
            
            # Завершаем задачу
            if self.complete_task(task_id, result, task):
                logging.info(f"Задача {task_id} успешно завершена")
                return True
            else:
                logging.error(f"Не удалось завершить задачу {task_id}")
                return False
                
        except Exception as e:
            logging.error(f"Ошибка при обработке задачи {task_id}: {e}")
            self.fail_task(task_id, str(e))
            return False
    
    def run(self):
        """Основной цикл обработки задач"""
        logging.info("Запуск AI процессора задач")
        
        while True:
            try:
                # Получаем задачи для обработки
                tasks = self.get_pending_tasks()
                
                if tasks:
                    logging.info(f"Найдено {len(tasks)} задач для обработки")
                    
                    for task in tasks:
                        self.process_task(task)
                        time.sleep(5)  # Небольшая пауза между задачами
                else:
                    logging.info("Нет задач для обработки")
                
                # Ждем перед следующей проверкой
                time.sleep(self.check_interval)
                
            except KeyboardInterrupt:
                logging.info("Получен сигнал остановки")
                break
            except Exception as e:
                logging.error(f"Ошибка в основном цикле: {e}")
                time.sleep(self.check_interval)

    def run_once(self):
        """Один прогон обработки задач (для cron)"""
        logging.info("Одноразовый запуск AI процессора задач")
        try:
            tasks = self.get_pending_tasks()
            if tasks:
                logging.info(f"Найдено {len(tasks)} задач для обработки")
                for task in tasks:
                    self.process_task(task)
                    time.sleep(2)
            else:
                logging.info("Нет задач для обработки")
        except Exception as e:
            logging.error(f"Ошибка в одноразовом запуске: {e}")

def main():
    """Точка входа"""
    # Настройки
    api_url = os.getenv('API_URL', 'https://cherkas-therapy.ru/api')
    api_key = os.getenv('API_KEY')
    
    # Проверяем наличие OpenAI API ключа
    if not os.getenv('OPENAI_API_KEY'):
        logging.error("Не установлен OPENAI_API_KEY")
        return
    
    # Создаем и запускаем процессор
    logging.info(f"Using API URL: {api_url}")
    processor = AITaskProcessor(api_url, api_key)
    run_once = os.getenv('RUN_ONCE', '0').lower() in ('1', 'true', 'yes')
    if run_once:
        processor.run_once()
    else:
        processor.run()

if __name__ == "__main__":
    main()