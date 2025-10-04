#!/usr/bin/env python3
"""
Модуль для написания адаптированных психологических статей
"""

import json
import logging
from typing import Dict, List, Optional
from openai import OpenAI
import os
from dotenv import load_dotenv
import re

load_dotenv()

class ArticleWriter:
    def __init__(self):
        self.client = OpenAI(
            api_key=os.getenv('OPENAI_API_KEY'),
            timeout=300  # 5 минут для написания статьи
        )
        # Настройки моделей для оптимизации расходов
        self.writing_model = os.getenv('WRITING_MODEL', 'gpt-3.5-turbo')  # По умолчанию дешевая модель
        self.use_gpt4_for_complex = os.getenv('USE_GPT4_FOR_COMPLEX', 'false').lower() == 'true'
    
    def _select_model_for_topic(self, theme: str) -> str:
        """Выбираем модель в зависимости от сложности темы"""
        if not self.use_gpt4_for_complex:
            return self.writing_model
        
        # Сложные темы, требующие GPT-4
        complex_topics = [
            'травма', 'депрессия', 'тревожность', 'кризис', 'отношения',
            'семья', 'дети', 'секс', 'любовь', 'развод', 'потеря',
            'психология', 'терапия', 'лечение', 'диагностика'
        ]
        
        theme_lower = theme.lower()
        for topic in complex_topics:
            if topic in theme_lower:
                return "gpt-4"
        
        return self.writing_model
        
    def write_adapted_article(self, analysis: Dict) -> Optional[Dict]:
        """Написать адаптированную статью на основе анализа"""
        try:
            prompt = self._build_writing_prompt(analysis)
            
            # Выбираем модель в зависимости от сложности темы
            model_to_use = self._select_model_for_topic(analysis.get('theme', ''))
            
            logging.info(f"Используем модель: {model_to_use} для темы: {analysis.get('theme', '')}")
            
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[
                    {
                        "role": "system", 
                        "content": """Ты пишешь ЦЕЛЬНЫЕ психологические статьи. 
Важно: одна тема = одна статья, без повторений. 
Используй естественные примеры, говори как опытный психолог с обычными людьми."""
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=4000,
                temperature=0.7,  # Снижаем температуру для более структурированного ответа
                top_p=0.9
            )
            
            article_content = response.choices[0].message.content.strip()
            
            # ВАЖНО: Проверяем структуру перед обработкой
            if not self._validate_article_structure(article_content):
                logging.warning("Статья содержит дубликаты, перегенерируем...")
                return self._regenerate_article_with_strict_rules(analysis)
            
            if not self._check_content_duplication(article_content):
                logging.warning("Найдены дублирующиеся параграфы, перегенерируем...")
                return self._regenerate_article_with_strict_rules(analysis)
            
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при написании статьи: {e}")
            return None
    
    def _build_writing_prompt(self, analysis: Dict) -> str:
        """Построить промпт для написания ЦЕЛЬНОЙ статьи"""
        return f"""
Ты опытный психолог и писатель, специализирующийся на создании полезных статей для обычных людей. 

НАПИШИ ОДНУ ЦЕЛЬНУЮ СТАТЬЮ на тему: "{analysis['main_theme']}"

**КЛЮЧЕВЫЕ АСПЕКТЫ:**
- Основная идея: {analysis['main_message']}
- Эмоциональный тон: {analysis['emotional_tone']}
- Целевая аудитория: обычные люди, интересующиеся психологией

**СТРУКТУРА СТАТЬИ (соблюдай строго эту последовательность):**

<h1>Заголовок (максимально цепляющий и полезный)</h1>

<h2>Введение: почему это важно</h2>
[2-3 абзаца, объясни актуальность темы через реальную жизненную ситуацию]

<h2>Суть проблемы: что на самом деле происходит</h2>
[3-4 абзаца, глубокий анализ без упрощений]
- Используй факты: {analysis['interesting_facts']}
- Раскрой скрытые аспекты: {analysis['hidden_truths']}

<h2>Практическое решение: конкретные шаги</h2>
[4-5 абзацев, детальные инструкции]
- Основные техники: {analysis['practical_advice']}
- Примеры применения в повседневной жизни
- Предупреждения о возможных трудностях

<h2>Ответы на частые вопросы</h2>
[2-3 вопроса с развернутыми ответами]

<h2>Заключение: главное запомнить</h2>
[1-2 абзаца, мотивирующий итог]

**ТРЕБОВАНИЯ К ФОРМАТУ:**
- Объем: 4000-6000 символов БЕЗ пробелов
- Каждый раздел должен быть РАЗВЕРНУТЫМ и законченным
- Между разделами - плавные переходы
- НИКАКИХ повторяющихся разделов
- HTML-разметка: h1, h2, h3, p, ul/li

**СТИЛИСТИЧЕСКИЕ ПРАВИЛА:**
- Естественный язык без психологического жаргона
- Примеры из обычной жизни (без указания "примеры из российской жизни")
- Практические советы, которые можно применить сразу
- Эмпатичный и поддерживающий тон

**ЗАПРЕЩЕНО:**
- Дублировать разделы с одинаковым содержанием
- Создавать несколько введений или заключений
- Упоминать "российский контекст" явно
- Делать статью короче 4000 символов

Начни сразу с HTML-разметки, без лишних комментариев.
"""
    
    def _process_article_content(self, content: str, analysis: Dict) -> Dict:
        """Обработать написанную статью"""
        try:
            # Извлекаем заголовок
            title = self._extract_title(content)
            
            # Очищаем контент
            cleaned_content = self._clean_html_content(content)
            
            # Проверяем минимальную длину
            content_length = len(cleaned_content.replace(' ', ''))
            if content_length < 4000:
                logging.warning(f"Статья слишком короткая: {content_length} символов (минимум 4000)")
                # Перегенерируем с более строгими правилами
                return self._regenerate_article_with_strict_rules(analysis)
            
            # Генерируем метаданные
            meta_title = self._generate_meta_title(title, analysis)
            meta_description = self._generate_meta_description(cleaned_content)
            excerpt = self._generate_excerpt(cleaned_content)
            
            # Определяем категорию
            category = self._determine_category(analysis)
            
            # Генерируем теги
            tags = self._generate_tags(analysis, cleaned_content)
            
            # Создаем FAQ
            faq = self._generate_faq(analysis, cleaned_content)
            
            # Принудительно расширяем контент если он все еще короткий
            if content_length < 5000:
                cleaned_content = self._expand_content(cleaned_content, analysis)
                content_length = len(cleaned_content.replace(' ', ''))
                logging.info(f"Контент расширен: {content_length} символов")
            else:
                logging.info(f"Статья создана: {content_length} символов")
            
            return {
                'title': title,
                'content': cleaned_content,
                'excerpt': excerpt,
                'meta_title': meta_title,
                'meta_description': meta_description,
                'category': category,
                'tags': tags,
                'faq': faq,
                'word_count': content_length,
                'original_analysis': analysis
            }
            
        except Exception as e:
            logging.error(f"Ошибка при обработке контента статьи: {e}")
            return None
    
    def _regenerate_longer_article(self, analysis: Dict) -> Dict:
        """Перегенерировать статью с более строгими требованиями к длине"""
        try:
            logging.info("Перегенерируем статью с более строгими требованиями к длине")
            
            # Создаем более строгий промпт
            strict_prompt = f"""
{self._build_writing_prompt(analysis)}

🚨 **КРИТИЧЕСКИ ВАЖНО:**
- Статья ДОЛЖНА быть минимум 5,000 символов (без пробелов)
- Если получится короче - статья будет отклонена
- Каждый раздел должен содержать минимум 2-3 абзаца
- Добавляй больше примеров, историй, кейсов
- Используй подразделы h3 для детализации
- Каждый абзац должен содержать минимум 2-3 предложения
- Включай больше практических советов и техник
- НЕ используй фразы "Примеры из российской жизни"
- НЕ упоминай "российское общество" или "семьи в России"
- Избегай повторений разделов
- Каждый раздел должен быть уникальным

**СТРУКТУРА ДЛЯ ДЛИННОЙ СТАТЬИ:**
<h1>Заголовок</h1>
<p>Введение (3-4 абзаца)</p>

<h2>Раздел 1</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>
<h3>Подраздел 1.1</h3>
<p>Детализация</p>

<h2>Раздел 2</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>
<h3>Подраздел 2.1</h3>
<p>Детализация</p>

<h2>Раздел 3</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>

<h2>Заключение</h2>
<p>Заключительные абзацы</p>
"""
            
            # Выбираем модель
            model_to_use = self._select_model_for_topic(analysis.get('theme', ''))
            
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[
                    {
                        "role": "system", 
                        "content": """Ты опытный психолог и писатель, специализирующийся на создании ОЧЕНЬ ДЛИННЫХ и детальных статей для обычных людей. 

Твои принципы:
- Пиши с эмпатией и пониманием
- Используй живые примеры из обычной жизни
- Избегай западных клише, имен, ситуаций
- Говори о том, о чем обычно молчат психологи
- Давай практические, выполнимые советы
- Создавай статьи, которые действительно помогают людям
- ВСЕГДА пиши длинные, детальные статьи минимум 5,000 символов
- Пиши естественно, без навязывания контекста"""
                    },
                    {"role": "user", "content": strict_prompt}
                ],
                max_tokens=4000,
                temperature=0.8,
                top_p=0.95
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при перегенерации статьи: {e}")
            return None
    
    def _expand_content(self, content: str, analysis: Dict) -> str:
        """Принудительно расширить контент статьи"""
        try:
            logging.info("Принудительно расширяем контент статьи")
            
            # Находим разделы для расширения
            sections = re.findall(r'<h2[^>]*>(.*?)</h2>', content, re.IGNORECASE | re.DOTALL)
            
            if not sections:
                return content
            
            # Выбираем первый раздел для расширения
            first_section = sections[0] if sections else "Практические советы"
            
            expansion_prompt = f"""
Расширь этот раздел статьи, добавив больше деталей, примеров и практических советов:

РАЗДЕЛ: {first_section}

ТЕМА СТАТЬИ: {analysis.get('main_theme', '')}
ОСНОВНОЕ СООБЩЕНИЕ: {analysis.get('main_message', '')}

ТРЕБОВАНИЯ:
- Добавь минимум 500-800 символов к этому разделу
- Включи конкретные примеры из обычной жизни
- Добавь практические техники и упражнения
- Используй подразделы h3 для структурирования
- Сохрани HTML форматирование

ФОРМАТ ОТВЕТА:
Начни сразу с HTML-разметки для расширения раздела.
"""
            
            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты опытный психолог, который расширяет статьи, добавляя больше деталей и практических советов для обычных людей."
                    },
                    {"role": "user", "content": expansion_prompt}
                ],
                max_tokens=2000,
                temperature=0.8
            )
            
            expansion = response.choices[0].message.content.strip()
            
            # Вставляем расширение в контент
            # Находим место после первого h2
            h2_pattern = r'(<h2[^>]*>.*?</h2>)'
            match = re.search(h2_pattern, content, re.IGNORECASE | re.DOTALL)
            
            if match:
                insert_pos = match.end()
                expanded_content = content[:insert_pos] + "\n" + expansion + "\n" + content[insert_pos:]
                return expanded_content
            else:
                return content + "\n" + expansion
                
        except Exception as e:
            logging.error(f"Ошибка при расширении контента: {e}")
            return content
    
    def _validate_article_structure(self, content: str) -> bool:
        """Проверить, что статья цельная без дубликатов"""
        # Ищем дублирующиеся разделы
        sections = re.findall(r'<h[12][^>]*>(.*?)</h[12]>', content, re.IGNORECASE | re.DOTALL)
        
        # Проверяем на повторяющиеся заголовки
        unique_sections = set()
        for section in sections:
            clean_section = re.sub(r'<[^>]+>', '', section).strip().lower()
            if clean_section in unique_sections:
                logging.warning(f"Найден дублирующийся раздел: {clean_section}")
                return False
            unique_sections.add(clean_section)
        
        # Проверяем минимальное количество разделов
        if len(unique_sections) < 4:
            logging.warning(f"Слишком мало разделов: {len(unique_sections)}")
            return False
        
        return True

    def _check_content_duplication(self, content: str) -> bool:
        """Проверить контент на дублирование абзацев"""
        paragraphs = re.findall(r'<p[^>]*>(.*?)</p>', content, re.IGNORECASE | re.DOTALL)
        
        # Проверяем уникальность первых 100 символов каждого параграфа
        paragraph_starts = set()
        for p in paragraphs:
            clean_p = re.sub(r'<[^>]+>', '', p).strip()[:100]
            if clean_p and len(clean_p) > 20:  # Игнорируем очень короткие параграфы
                if clean_p in paragraph_starts:
                    logging.warning("Найдены дублирующиеся параграфы")
                    return False
                paragraph_starts.add(clean_p)
        
        return True

    def _regenerate_article_with_strict_rules(self, analysis: Dict) -> Dict:
        """Перегенерировать статью с более строгими правилами"""
        try:
            logging.info("Перегенерируем статью с более строгими правилами")
            
            strict_prompt = f"""
{self._build_writing_prompt(analysis)}

🚨 **КРИТИЧЕСКИ ВАЖНО:**
- Статья ДОЛЖНА быть минимум 4000 символов (без пробелов)
- Если получится короче - статья будет отклонена
- Каждый раздел должен содержать минимум 2-3 абзаца
- Добавляй больше примеров, историй, кейсов
- Используй подразделы h3 для детализации
- Каждый абзац должен содержать минимум 2-3 предложения
- Включай больше практических советов и техник
- НЕ используй фразы "Примеры из российской жизни"
- НЕ упоминай "российское общество" или "семьи в России"
- Избегай повторений разделов
- Каждый раздел должен быть уникальным

**СТРУКТУРА ДЛЯ ДЛИННОЙ СТАТЬИ:**
<h1>Заголовок</h1>
<p>Введение (3-4 абзаца)</p>

<h2>Раздел 1</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>
<h3>Подраздел 1.1</h3>
<p>Детализация</p>

<h2>Раздел 2</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>
<h3>Подраздел 2.1</h3>
<p>Детализация</p>

<h2>Раздел 3</h2>
<p>Абзац 1</p>
<p>Абзац 2</p>
<p>Абзац 3</p>

<h2>Заключение</h2>
<p>Заключительные абзацы</p>
"""
            
            # Выбираем модель
            model_to_use = self._select_model_for_topic(analysis.get('theme', ''))
            
            response = self.client.chat.completions.create(
                model=model_to_use,
                messages=[
                    {
                        "role": "system", 
                        "content": """Ты опытный психолог и писатель, специализирующийся на создании ОЧЕНЬ ДЛИННЫХ и детальных статей для обычных людей. 

Твои принципы:
- Пиши с эмпатией и пониманием
- Используй живые примеры из обычной жизни
- Избегай западных клише, имен, ситуаций
- Говори о том, о чем обычно молчат психологи
- Давай практические, выполнимые советы
- Создавай статьи, которые действительно помогают людям
- ВСЕГДА пиши длинные, детальные статьи минимум 4000 символов
- Пиши естественно, без навязывания контекста"""
                    },
                    {"role": "user", "content": strict_prompt}
                ],
                max_tokens=4000,
                temperature=0.7,  # Снижаем температуру для более структурированного ответа
                top_p=0.9
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при перегенерации статьи: {e}")
            return None
    
    def _generate_article_by_parts(self, analysis: Dict) -> Dict:
        """Генерировать статью по частям (план → разделы → объединение)"""
        try:
            logging.info("Генерируем статью по частям")
            
            # Шаг 1: Генерируем план статьи
            plan_prompt = f"""
Создай детальный план статьи на тему: {analysis['main_theme']}

ОСНОВНОЕ СООБЩЕНИЕ: {analysis['main_message']}
ТОН: {analysis['emotional_tone']}

ТРЕБОВАНИЯ К ПЛАНУ:
- 5-6 основных разделов
- Каждый раздел должен содержать 2-3 подраздела
- План должен быть детальным и структурированным
- Учитывай особенности обычной жизни

ФОРМАТ ОТВЕТА:
1. Введение
   1.1. Эмоциональный крючок
   1.2. Постановка проблемы
   1.3. Что будет в статье

2. [Название раздела]
   2.1. [Подраздел]
   2.2. [Подраздел]
   2.3. [Подраздел]

3. [Название раздела]
   3.1. [Подраздел]
   3.2. [Подраздел]

И так далее...
"""
            
            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты опытный психолог, который создает детальные планы статей для обычных людей."
                    },
                    {"role": "user", "content": plan_prompt}
                ],
                max_tokens=2000,
                temperature=0.7
            )
            
            plan = response.choices[0].message.content.strip()
            logging.info(f"План создан: {len(plan)} символов")
            
            # Шаг 2: Генерируем разделы по отдельности
            sections = []
            
            # Извлекаем разделы из плана
            section_matches = re.findall(r'(\d+\.\s+[^\n]+)', plan)
            
            for i, section_title in enumerate(section_matches[:4]):  # Ограничиваем 4 разделами
                section_prompt = f"""
Напиши раздел статьи на основе этого плана:

ПЛАН СТАТЬИ:
{plan}

РАЗДЕЛ ДЛЯ НАПИСАНИЯ: {section_title}

ТЕМА СТАТЬИ: {analysis['main_theme']}
ОСНОВНОЕ СООБЩЕНИЕ: {analysis['main_message']}
ТОН: {analysis['emotional_tone']}

ТРЕБОВАНИЯ:
- Напиши только этот раздел
- Минимум 800-1200 символов
- Используй HTML разметку (h2, h3, p)
- Включи примеры из обычной жизни
- Добавь практические советы

ФОРМАТ ОТВЕТА:
<h2>Название раздела</h2>
<p>Первый абзац...</p>
<p>Второй абзац...</p>
<h3>Подраздел</h3>
<p>Детализация...</p>
"""
                
                response = self.client.chat.completions.create(
                    model="gpt-3.5-turbo",
                    messages=[
                        {
                            "role": "system", 
                            "content": "Ты опытный психолог, который пишет детальные разделы статей для обычных людей."
                        },
                        {"role": "user", "content": section_prompt}
                    ],
                    max_tokens=2000,
                    temperature=0.8
                )
                
                section_content = response.choices[0].message.content.strip()
                sections.append(section_content)
                logging.info(f"Раздел {i+1} создан: {len(section_content)} символов")
            
            # Шаг 3: Объединяем все части
            full_content = f"<h1>{analysis['main_theme']}</h1>\n\n"
            full_content += "\n\n".join(sections)
            
            # Шаг 4: Обрабатываем как обычную статью
            return self._process_article_content(full_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при генерации статьи по частям: {e}")
            return None
    
    def _extract_title(self, content: str) -> str:
        """Извлечь заголовок из HTML"""
        h1_match = re.search(r'<h1[^>]*>(.*?)</h1>', content, re.IGNORECASE | re.DOTALL)
        if h1_match:
            title = h1_match.group(1).strip()
            # Убираем HTML теги из заголовка
            title = re.sub(r'<[^>]+>', '', title).strip()
            return title
        return "Психологическая статья"
    
    def _clean_html_content(self, content: str) -> str:
        """Очистить HTML контент"""
        # Убираем ```html в начале
        content = re.sub(r'^```html\s*', '', content, flags=re.MULTILINE)
        content = re.sub(r'^```\s*$', '', content, flags=re.MULTILINE)
        
        # Убираем лишние пробелы и переносы
        content = re.sub(r'\n\s*\n', '\n', content)
        content = re.sub(r' +', ' ', content)
        
        # Убираем пустые параграфы
        content = re.sub(r'<p>\s*</p>', '', content)
        
        # Исправляем списки - каждый пункт с новой строки
        content = re.sub(r'(\d+\.\s*[^<\n]+)(?=\s*\d+\.)', r'\1\n', content)
        
        return content.strip()
    
    def _generate_meta_title(self, title: str, analysis: Dict) -> str:
        """Генерировать мета-заголовок"""
        if len(title) <= 60:
            return title
        return title[:57] + "..."
    
    def _generate_meta_description(self, content: str) -> str:
        """Генерировать мета-описание"""
        # Убираем HTML теги
        text = re.sub(r'<[^>]+>', '', content)
        # Берем первые 160 символов
        description = text[:160].strip()
        if len(text) > 160:
            description = description.rsplit(' ', 1)[0] + "..."
        return description
    
    def _generate_excerpt(self, content: str) -> str:
        """Генерировать краткое описание"""
        # Убираем HTML теги
        text = re.sub(r'<[^>]+>', '', content)
        # Берем первые 200 символов
        excerpt = text[:200].strip()
        if len(text) > 200:
            excerpt = excerpt.rsplit(' ', 1)[0] + "..."
        return excerpt
    
    def _determine_category(self, analysis: Dict) -> str:
        """Определить категорию статьи"""
        theme = analysis['main_theme'].lower()
        
        if any(word in theme for word in ['отношения', 'любовь', 'семья', 'брак']):
            return 'Отношения'
        elif any(word in theme for word in ['тревога', 'депрессия', 'стресс', 'паника']):
            return 'Стресс и тревога'
        elif any(word in theme for word in ['дети', 'родители', 'воспитание']):
            return 'Детская психология'
        elif any(word in theme for word in ['рост', 'мотивация', 'привычки', 'развитие']):
            return 'Саморазвитие'
        else:
            return 'Психология'
    
    def _generate_tags(self, analysis: Dict, content: str) -> List[str]:
        """Генерировать теги для статьи"""
        tags = []
        
        # Добавляем теги на основе темы
        theme = analysis['main_theme'].lower()
        if 'тревога' in theme:
            tags.extend(['тревога', 'беспокойство', 'самопомощь'])
        elif 'отношения' in theme:
            tags.extend(['отношения', 'семья', 'любовь'])
        elif 'дети' in theme:
            tags.extend(['дети', 'родители', 'воспитание'])
        
        # Добавляем общие теги
        tags.extend(['психология', 'самопомощь', 'психическое здоровье'])
        
        # Убираем дубликаты и ограничиваем количество
        return list(set(tags))[:5]
    
    def _generate_faq(self, analysis: Dict, content: str) -> List[Dict]:
        """Генерировать FAQ для статьи"""
        faq = []
        
        # Базовые вопросы на основе темы
        theme = analysis['main_theme'].lower()
        
        if 'тревога' in theme:
            faq.extend([
                {
                    'question': 'Как понять, что у меня тревожное расстройство?',
                    'answer': 'Если тревога мешает повседневной жизни более 6 месяцев, стоит обратиться к специалисту.'
                },
                {
                    'question': 'Можно ли справиться с тревогой самостоятельно?',
                    'answer': 'Да, многие техники самопомощи эффективны, но в сложных случаях нужна профессиональная поддержка.'
                }
            ])
        elif 'отношения' in theme:
            faq.extend([
                {
                    'question': 'Как понять, что отношения токсичны?',
                    'answer': 'Если отношения приносят больше боли, чем радости, и вы чувствуете себя истощенным.'
                },
                {
                    'question': 'Можно ли спасти отношения?',
                    'answer': 'Да, если оба партнера готовы работать над собой и отношениями.'
                }
            ])
        
        return faq[:3]  # Максимум 3 вопроса
    
    def write_multiple_articles(self, analyses: List[Dict]) -> List[Dict]:
        """Написать несколько статей"""
        articles = []
        
        for i, analysis in enumerate(analyses, 1):
            logging.info(f"Пишу статью {i}/{len(analyses)}: {analysis['main_theme']}")
            
            article = self.write_adapted_article(analysis)
            if article:
                articles.append(article)
            else:
                logging.warning(f"Не удалось написать статью для анализа: {analysis['main_theme']}")
        
        logging.info(f"Успешно написано {len(articles)} из {len(analyses)} статей")
        return articles

if __name__ == "__main__":
    # Тестирование писателя
    logging.basicConfig(level=logging.INFO)
    
    # Пример анализа для тестирования
    test_analysis = {
        'main_theme': 'Тревога в отношениях',
        'core_narrative': 'Многие люди испытывают тревогу в близких отношениях',
        'main_message': 'Тревога в отношениях - это нормально, и с ней можно справиться',
        'interesting_facts': ['Тревога в отношениях встречается у 40% людей'],
        'hidden_truths': ['Психологи часто не говорят о том, что тревога может быть защитой'],
        'practical_advice': ['Практикуйте техники заземления', 'Общайтесь открыто с партнером'],
        'emotional_tone': 'поддерживающий',
        'target_audience': 'Люди в отношениях',
        'cultural_adaptation_notes': 'Учесть особенности российского менталитета',
        'article_structure': {
            'introduction_approach': 'Начать с истории из российской жизни',
            'problem_presentation': 'Описать проблему доступно',
            'solution_approach': 'Дать практические советы',
            'conclusion_style': 'Мотивирующее заключение'
        },
        'local_examples_needed': ['Российские примеры'],
        'sensitivity_notes': 'Учесть особенности культуры'
    }
    
    writer = ArticleWriter()
    article = writer.write_adapted_article(test_analysis)
    
    if article:
        print("Статья написана:")
        print(f"Заголовок: {article['title']}")
        print(f"Категория: {article['category']}")
        print(f"Теги: {', '.join(article['tags'])}")
        print(f"Слов: {article['word_count']}")
    else:
        print("Ошибка написания статьи")
