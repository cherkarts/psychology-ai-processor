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
        """Написать адаптированную статью"""
        try:
            # Сначала пробуем обычный промпт
            article = self._try_generate_article(analysis)
            if article:
                return article
            
            # Если не получилось - принудительная генерация
            logging.info("Обычная генерация не удалась, используем принудительную")
            return self._force_long_article(analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при написании статьи: {e}")
            return None
    
    def _build_writing_prompt(self, analysis: Dict) -> str:
        """Финальный промпт с четкими указаниями"""
        return f"""
НАПИШИ ДЛИННУЮ СТАТЬЮ НА ТЕМУ: "{analysis['main_theme']}"

ОСНОВНАЯ ИДЕЯ: {analysis['main_message']}

ТРЕБОВАНИЯ:
- ОБЪЕМ: МИНИМУМ 6000 символов
- ФОРМАТ: ЧИСТЫЙ ТЕКСТ БЕЗ РАЗМЕТКИ
- СТИЛЬ: Естественный, как будто пишешь для блога

СТРУКТУРА (соблюдай точно):

ЗАГОЛОВОК СТАТЬИ
(пустая строка)

ВВЕДЕНИЕ
(3-4 абзаца о важности темы, начни с реальной жизненной ситуации)

ПРОБЛЕМА И АНАЛИЗ  
(4-5 абзацев, глубокий разбор причин и механизмов)

ПРАКТИЧЕСКИЕ РЕШЕНИЯ
(5-6 абзацев с конкретными техниками и примерами)

ЧАСТЫЕ ВОПРОСЫ
(3-4 вопроса с развернутыми ответами)

ЗАКЛЮЧЕНИЕ
(2-3 мотивирующих абзаца)

ПРАВИЛА:
- НИКАКОЙ HTML-РАЗМЕТКИ
- НИКАКОГО MARKDOWN (**жирный** и т.д.)
- НИКАКИХ ЗВЕЗДОЧЕК, РАЗДЕЛИТЕЛЕЙ
- ТОЛЬКО ЧИСТЫЙ ТЕКСТ С АБЗАЦАМИ
- Абзацы разделяй одной пустой строкой
- Заголовки разделов пиши ПРОПИСНЫМИ буквами

ПРИМЕР ПРАВИЛЬНОГО ФОРМАТА:
Заголовок статьи

ВВЕДЕНИЕ
Текст первого абзаца введения...

Текст второго абзаца введения...

ПРОБЛЕМА И АНАЛИЗ
Текст первого абзаца анализа...

Начни писать сразу с заголовка статьи!
"""
    
    def _process_article_content(self, content: str, analysis: Dict) -> Dict:
        """Обработка чистого текста"""
        try:
            # Очищаем от любой разметки
            cleaned_content = self._clean_all_markup(content)
            
            # Проверяем длину
            content_length = len(cleaned_content)
            logging.info(f"Длина статьи: {content_length} символов")
            
            if content_length < 4000:
                logging.warning(f"Статья короткая: {content_length}")
                return self._generate_with_gpt4(analysis)
            
            # Преобразуем в правильный HTML
            html_content = self._convert_plain_to_proper_html(cleaned_content)
            
            # Генерируем метаданные
            title = self._extract_clean_title(cleaned_content)
            
            return {
                'title': title,
                'content': html_content,
                'excerpt': self._generate_excerpt(cleaned_content),
                'meta_title': title,
                'meta_description': self._generate_meta_description(cleaned_content),
                'category': self._determine_category(analysis),
                'tags': self._generate_tags(analysis, cleaned_content),
                'faq': [],
                'word_count': content_length,
                'original_analysis': analysis
            }
            
        except Exception as e:
            logging.error(f"Ошибка обработки: {e}")
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
        """Упрощенная проверка структуры"""
        # Считаем абзацы (разделы по пустым строкам)
        paragraphs = [p for p in content.split('\n\n') if p.strip()]
        
        # Минимум 8 абзацев для длинной статьи
        if len(paragraphs) < 8:
            logging.warning(f"Слишком мало абзацев: {len(paragraphs)}")
            return False
        
        # Проверяем длину
        if len(content) < 6000:
            logging.warning(f"Статья слишком короткая: {len(content)} символов")
            return False
        
        return True

    def _check_content_duplication(self, content: str) -> bool:
        """Упрощенная проверка на дублирование"""
        paragraphs = [p for p in content.split('\n\n') if p.strip()]
        
        # Проверяем первые 100 символов каждого абзаца на уникальность
        paragraph_starts = set()
        for p in paragraphs:
            start = p[:100].strip()
            if len(start) > 30:  # Игнорируем очень короткие
                if start in paragraph_starts:
                    logging.warning("Найдены дублирующиеся абзацы")
                    return False
                paragraph_starts.add(start)
        
        return True

    def _clean_all_markup(self, content: str) -> str:
        """Очистка от всей разметки"""
        # Убираем HTML теги
        content = re.sub(r'<[^>]+>', '', content)
        # Убираем Markdown (**жирный**)
        content = re.sub(r'\*\*([^*]+)\*\*', r'\1', content)
        content = re.sub(r'\*([^*]+)\*', r'\1', content)
        # Убираем лишние пустые строки
        content = re.sub(r'\n\s*\n', '\n\n', content)
        return content.strip()

    def _convert_plain_to_proper_html(self, content: str) -> str:
        """Преобразование чистого текста в красивый HTML"""
        lines = content.split('\n')
        html_parts = []
        
        i = 0
        while i < len(lines):
            line = lines[i].strip()
            
            if not line:
                i += 1
                continue
                
            # Заголовок статьи (первая непустая строка)
            if i == 0:
                html_parts.append(f'<h1>{line}</h1>')
                i += 1
                continue
                
            # Заголовки разделов (прописные буквы)
            if (line.isupper() and 
                any(keyword in line for keyword in ['ВВЕДЕНИЕ', 'ПРОБЛЕМА', 'РЕШЕНИЯ', 'ВОПРОСЫ', 'ЗАКЛЮЧЕНИЕ', 'АНАЛИЗ'])):
                
                # Определяем уровень заголовка
                if 'ВВЕДЕНИЕ' in line:
                    html_parts.append(f'<h2>Введение</h2>')
                elif any(x in line for x in ['ПРОБЛЕМА', 'АНАЛИЗ']):
                    html_parts.append(f'<h2>Проблема и анализ</h2>')
                elif 'РЕШЕНИЯ' in line:
                    html_parts.append(f'<h2>Практические решения</h2>')
                elif 'ВОПРОСЫ' in line:
                    html_parts.append(f'<h2>Частые вопросы</h2>')
                elif 'ЗАКЛЮЧЕНИЕ' in line:
                    html_parts.append(f'<h2>Заключение</h2>')
                else:
                    html_parts.append(f'<h2>{line.title()}</h2>')
                    
                i += 1
                continue
                
            # Обычные абзацы
            if line and not line.isupper():
                # Собираем многострочный абзац
                paragraph = line
                i += 1
                while i < len(lines) and lines[i].strip() and not lines[i].strip().isupper():
                    paragraph += ' ' + lines[i].strip()
                    i += 1
                
                # Форматируем вопросы-ответы
                if paragraph.strip().startswith('*') or paragraph.strip().endswith('?'):
                    html_parts.append(f'<p><strong>{paragraph.strip(" *")}</strong></p>')
                else:
                    html_parts.append(f'<p>{paragraph}</p>')
                continue
                
            i += 1
        
        return '\n\n'.join(html_parts)

    def _extract_clean_title(self, content: str) -> str:
        """Извлечь чистый заголовок"""
        first_line = content.split('\n')[0].strip()
        # Убираем остатки разметки
        title = re.sub(r'[**]', '', first_line)
        return title[:100]  # Ограничиваем длину

    def _generate_with_gpt4(self, analysis: Dict) -> Dict:
        """Генерация через GPT-4 как запасной вариант"""
        try:
            simple_prompt = f"""
Напиши длинную статью на тему "{analysis['main_theme']}".

Основная идея: {analysis['main_message']}

Требования:
- Объем: минимум 5000 символов
- Формат: чистый текст без любой разметки
- Структура: Заголовок, Введение, Основная часть, Решения, Заключение
- Абзацы разделяй одной пустой строкой

Пиши естественно и подробно.
"""

            response = self.client.chat.completions.create(
                model="gpt-4",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты пишешь качественные статьи в чистом текстовом формате. Без разметки, без форматирования."
                    },
                    {"role": "user", "content": simple_prompt}
                ],
                max_tokens=4000,
                temperature=0.7
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка GPT-4: {e}")
            return None

    def _clean_plain_content(self, content: str) -> str:
        """Очистка простого текста"""
        # Убираем ```markdown и подобное
        content = re.sub(r'^```[a-z]*\s*', '', content, flags=re.MULTILINE)
        content = re.sub(r'^```\s*$', '', content, flags=re.MULTILINE)
        
        # Убираем лишние пробелы
        content = re.sub(r' +', ' ', content)
        content = re.sub(r'\n\s*\n', '\n\n', content)
        
        return content.strip()

    def _extract_title_from_text(self, content: str) -> str:
        """Извлечь заголовок из текста"""
        # Берем первую строку как заголовок
        first_line = content.split('\n')[0].strip()
        if len(first_line) < 100:  # Разумная длина для заголовка
            return first_line
        return "Психологическая статья"

    def _convert_to_html(self, content: str) -> str:
        """Преобразовать простой текст в HTML"""
        paragraphs = [p for p in content.split('\n\n') if p.strip()]
        
        if not paragraphs:
            return "<p>" + content + "</p>"
        
        html_parts = []
        
        # Первый параграф - заголовок
        if paragraphs:
            html_parts.append(f"<h1>{paragraphs[0]}</h1>")
        
        # Остальные параграфы
        for i, paragraph in enumerate(paragraphs[1:], 1):
            if i == 1:
                html_parts.append(f"<h2>Введение</h2>")
            elif i == len(paragraphs) - 1:
                html_parts.append(f"<h2>Заключение</h2>")
            
            html_parts.append(f"<p>{paragraph}</p>")
        
        return '\n\n'.join(html_parts)

    def _force_long_article(self, analysis: Dict) -> Dict:
        """Принудительно генерируем длинную статью"""
        try:
            logging.info("ПРИНУДИТЕЛЬНАЯ генерация длинной статьи")
            
            force_prompt = f"""
ТЕМА: {analysis['main_theme']}
ИДЕЯ: {analysis['main_message']}

ТЫ ДОЛЖЕН НАПИСАТЬ ОЧЕНЬ ДЛИННУЮ СТАТЬЮ!

ТРЕБОВАНИЕ: АБСОЛЮТНЫЙ МИНИМУМ 7000 СИМВОЛОВ!

РАСПИШИ КАЖДУЮ МЫСЛЬ МАКСИМАЛЬНО ПОДРОБНО:

1. ВВЕДЕНИЕ (4 абзаца по 7-8 предложений)
   - Начни с детального описания реальной ситуации
   - Объясни почему эта проблема актуальна для многих людей
   - Опиши эмоциональные переживания человека
   - Расскажи о масштабах проблемы в современном обществе

2. АНАЛИЗ (6 абзацев по 6-8 предложений)  
   - Детально разбери психологические механизмы
   - Используй научные факты: {analysis['interesting_facts']}
   - Раскрой скрытые аспекты: {analysis['hidden_truths']}
   - Объясни почему проблема возникает
   - Опиши как она развивается со временем
   - Расскажи о последствиях без решения

3. РЕШЕНИЯ (8 абзацев по 5-7 предложений)
   - Подробно опиши каждый совет: {analysis['practical_advice']}
   - Для каждой техники дай пошаговое руководство
   - Добавь конкретные примеры из разных жизненных ситуаций
   - Объясни почему каждый метод работает
   - Предупреди о возможных трудностях
   - Дай советы по преодолению сопротивления
   - Расскажи как отслеживать прогресс
   - Объясни когда нужно обращаться к специалисту

4. ВОПРОСЫ-ОТВЕТЫ (4 развернутых ответа по 2-3 абзаца каждый)

5. ЗАКЛЮЧЕНИЕ (3 мотивирующих абзаца по 6-7 предложений)

НЕ ЭКОНОМЬ НА СЛОВАХ! ПИШИ МАКСИМАЛЬНО РАЗВЕРНУТО!
КАЖДОЕ ПРЕДЛОЖЕНИЕ ДОЛЖНО БЫТЬ ИНФОРМАТИВНЫМ И ПОЛНЫМ!

НАЧНИ С ЗАГОЛОВКА И ПИШИ СПЛОШНЫМ ТЕКСТОМ!
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты пишешь ОЧЕНЬ ДЛИННЫЕ статьи. Твоя единственная задача - объем и детализация. Не экономь на словах!"
                    },
                    {"role": "user", "content": force_prompt}
                ],
                max_tokens=4000,
                temperature=0.9
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при принудительной генерации: {e}")
            return None

    def _try_generate_article(self, analysis: Dict) -> Optional[Dict]:
        """Попытка обычной генерации"""
        prompt = self._build_writing_prompt(analysis)
        
        response = self.client.chat.completions.create(
            model="gpt-3.5-turbo",
            messages=[
                {
                    "role": "system", 
                    "content": "Ты пишешь длинные, детальные психологические статьи. Главное - объем и полезность."
                },
                {"role": "user", "content": prompt}
            ],
            max_tokens=4000,
            temperature=0.8
        )
        
        article_content = response.choices[0].message.content.strip()
        return self._process_article_content(article_content, analysis)

    def _regenerate_article_with_strict_rules(self, analysis: Dict) -> Dict:
        """Перегенерировать статью с более строгими правилами"""
        try:
            logging.info("Перегенерируем статью с более строгими правилами")
            
            strict_prompt = f"""
{self._build_writing_prompt(analysis)}

🚨 **КРИТИЧЕСКИ ВАЖНО:**
- Статья ДОЛЖНА быть минимум 6000 символов (без пробелов)
- Если получится короче - статья будет отклонена
- Каждый раздел должен содержать минимум 3-4 абзаца
- Каждый абзац должен содержать минимум 5-7 предложений
- Добавляй больше примеров, историй, кейсов
- Используй подразделы h3 для детализации
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
- ВСЕГДА пиши длинные, детальные статьи минимум 6000 символов
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
