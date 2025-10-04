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
        """Адаптация ЛЮБОЙ темы с Psychology Today"""
        try:
            # Сначала пробуем быстрое решение
            quick_result = self.write_adapted_article_quick(analysis)
            if quick_result:
                logging.info("Быстрая генерация успешна")
                return quick_result
            
            # Если быстрое решение не сработало, используем умную адаптацию
            logging.info("Переходим к умной адаптации")
            
            # Берем реальную тему из анализа
            theme = analysis['main_theme']
            message = analysis['main_message']
            
            logging.info(f"Адаптируем тему: {theme}")
            
            # 1. Анализируем тип темы для выбора структуры
            theme_type = self._analyze_theme_type(theme)
            
            # 2. Генерируем подходящую структуру для этой темы
            structure = self._generate_structure_for_theme(theme, theme_type, analysis)
            
            # 3. Генерируем разделы по этой структуре
            sections = self._generate_theme_sections(theme, structure, analysis)
            
            # 4. Собираем статью
            full_content = self._build_theme_article(theme, sections, analysis)
            
            return self._process_final_article(full_content, analysis, len(full_content))
                
        except Exception as e:
            logging.error(f"Ошибка адаптации темы: {e}")
            return None

    def write_adapted_article_quick(self, analysis: Dict) -> Optional[Dict]:
        """Быстрое решение - гарантированно 4 раздела"""
        try:
            theme = analysis['main_theme']
            
            # Генерируем 4 раздела одним запросом с четкими инструкциями
            prompt = f"""
Напиши статью на тему "{theme}" объемом 4000-5000 символов.

ОСНОВНАЯ ИДЕЯ: {analysis['main_message']}

СТРОГО СОБЛЮДАЙ СТРУКТУРУ:

ЧАСТЬ 1: ВВЕДЕНИЕ (800-1000 символов)
- Статистика и масштабы проблемы
- Актуальность темы
- {analysis['interesting_facts']}

ЧАСТЬ 2: АНАЛИЗ (800-1000 символов)  
- Причины и механизмы
- {analysis['hidden_truths']}

ЧАСТЬ 3: РЕШЕНИЯ (1000-1200 символов)
- Конкретные техники и упражнения
- {analysis['practical_advice']}

ЧАСТЬ 4: ВЫВОДЫ (800-1000 символов)
- Профилактика и рекомендации
- Когда обращаться за помощью

ТРЕБОВАНИЯ К ФОРМАТУ:
- КАЖДУЮ ЧАСТЬ НАЧИНАЙ С "ЧАСТЬ X:" (например, "ЧАСТЬ 1: ВВЕДЕНИЕ")
- ПИШИ ПЛОТНЫЙ ИНФОРМАТИВНЫЙ ТЕКСТ БЕЗ ЛИШНИХ СЛОВ
- ИСПОЛЬЗУЙ ПОДРОБНЫЕ ПРИМЕРЫ И КОНКРЕТНЫЕ ФАКТЫ
- НЕ ИСПОЛЬЗУЙ HTML ИЛИ MARKDOWN РАЗМЕТКУ
- ПИШИ НА РУССКОМ ЯЗЫКЕ

Начни сразу с названия темы статьи, а затем следуй структуре выше.
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[{"role": "user", "content": prompt}],
                max_tokens=3500,
                temperature=0.7
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_final_article(article_content, analysis, len(article_content))
            
        except Exception as e:
            logging.error(f"Ошибка быстрой генерации: {e}")
            return None

    def write_adapted_article_quality(self, analysis: Dict) -> Optional[Dict]:
        """Генерация с акцентом на качество контента"""
        try:
            theme = analysis['main_theme']
            
            prompt = f"""
НАПИШИ КАЧЕСТВЕННУЮ СТАТЬЮ НА ТЕМУ: "{theme}"

ОСНОВНАЯ ИДЕЯ: {analysis['main_message']}

ИСПОЛЬЗУЙ КОНКРЕТНЫЕ ДАННЫЕ:
- Факты: {analysis['interesting_facts']}
- Важные аспекты: {analysis['hidden_truths']}
- Практические советы: {analysis['practical_advice']}

СТРУКТУРА СТАТЬИ:

ВВЕДЕНИЕ
- Начни с реальной жизненной ситуации
- Приведи статистику и масштабы проблемы
- Объясни почему тема актуальна именно сейчас

АНАЛИЗ ПРИЧИН
- Детально разбери психологические механизмы
- Объясни физиологические процессы в организме
- Раскрой скрытые причины которые обычно умалчивают

ПРАКТИЧЕСКИЕ ТЕХНИКИ
- Дай пошаговые инструкции для каждой техники
- Объясни КАК И ПОЧЕМУ это работает
- Приведи конкретные примеры применения в жизни

ПРОФИЛАКТИКА И ВЫВОДЫ
- Расскажи о долгосрочных стратегиях
- Объясни когда обращаться к специалисту
- Дай мотивирующие рекомендации

ТРЕБОВАНИЯ К КАЧЕСТВУ:
- КОНКРЕТИКА: вместо "некоторые техники" - названия конкретных методов
- ОБЪЯСНЕНИЯ: не просто "это работает", а почему это работает
- ПРИМЕРЫ: реальные жизненные ситуации
- ЯСНОСТЬ: простой язык без сложных терминов
- ОБЪЕМ: каждый раздел 800-1000 символов

Избегай общих фраз вроде "важно помнить", "следует отметить".
Пиши так, как будто объясняешь другу.
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты пишешь качественные психологические статьи с конкретными примерами и понятными объяснениями. Избегай общих фраз, давай практические советы."
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=3500,
                temperature=0.8
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_final_article(article_content, analysis, len(article_content))
            
        except Exception as e:
            logging.error(f"Ошибка генерации: {e}")
            return None

    def _analyze_theme_type(self, theme: str) -> str:
        """Определяем тип темы для выбора структуры"""
        theme_lower = theme.lower()
        
        if any(word in theme_lower for word in ['stress', 'anxiety', 'worry', 'panic', 'тревога', 'стресс']):
            return 'stress_anxiety'
        elif any(word in theme_lower for word in ['relationship', 'love', 'marriage', 'family', 'отношения', 'семья', 'любовь']):
            return 'relationships' 
        elif any(word in theme_lower for word in ['child', 'parent', 'teen', 'kids', 'дети', 'ребенок', 'родители']):
            return 'parenting'
        elif any(word in theme_lower for word in ['depression', 'mental health', 'therapy', 'депрессия', 'психическое', 'терапия']):
            return 'mental_health'
        elif any(word in theme_lower for word in ['happiness', 'success', 'motivation', 'goal', 'счастье', 'успех', 'мотивация']):
            return 'self_improvement'
        else:
            return 'general'

    def _generate_structure_for_theme(self, theme: str, theme_type: str, analysis: Dict) -> List[str]:
        """Генерируем структуру под конкретный тип темы"""
        
        structures = {
            'stress_anxiety': [
                "Реальная ситуация: как стресс проявляется в жизни",
                "Научное объяснение механизмов стресса", 
                "Практические техники для мгновенного облегчения",
                "Долгосрочные стратегии управления тревогой"
            ],
            
            'relationships': [
                "Типичные проблемы в отношениях на примерах",
                "Психологические причины конфликтов", 
                "Конкретные шаги для улучшения общения",
                "Как сохранять здоровые отношения"
            ],
            
            'parenting': [
                "Современные вызовы в воспитании детей",
                "Возрастные особенности и потребности",
                "Практические методы воспитания", 
                "Баланс между строгостью и поддержкой"
            ],
            
            'mental_health': [
                "Как распознать проблему: симптомы и признаки",
                "Профессиональные подходы к лечению",
                "Самопомощь и поддержка близких",
                "Профилактика и поддержание здоровья"
            ],
            
            'self_improvement': [
                "Почему это важно для качества жизни", 
                "Психологические барьеры и как их преодолеть",
                "Конкретные привычки и упражнения",
                "Как отслеживать прогресс и не сдаваться"
            ],
            
            'general': [
                "Актуальность и важность темы",
                "Глубинный анализ проблемы", 
                "Практические решения и методы",
                "Выводы и рекомендации"
            ]
        }
        
        return structures.get(theme_type, structures['general'])

    def _generate_theme_sections(self, theme: str, structure: List[str], analysis: Dict) -> List[str]:
        """Генерация разделов для конкретной темы"""
        sections = []
        previous_content = []
        
        for i, section_task in enumerate(structure):
            section_content = self._generate_section_for_theme(
                theme, section_task, i, analysis, previous_content
            )
            
            if section_content and self._is_unique_content(section_content, previous_content):
                sections.append(section_content)
                previous_content.append(section_content)
                logging.info(f"Раздел {i+1} создан: {len(section_content)} символов")
        
        return sections

    def _generate_section_for_theme(self, theme: str, section_task: str, index: int, 
                                   analysis: Dict, previous: List[str]) -> str:
        """Генерация одного раздела для темы"""
        try:
            # Собираем релевантные данные для этого раздела
            relevant_facts = self._select_relevant_facts(analysis['interesting_facts'], section_task)
            relevant_truths = self._select_relevant_truths(analysis['hidden_truths'], section_task)
            relevant_advice = self._select_relevant_advice(analysis['practical_advice'], section_task)
            
            prompt = f"""
ТЕМА СТАТЬИ: {theme}
ОСНОВНАЯ ИДЕЯ: {analysis['main_message']}

ЗАДАЧА ЭТОГО РАЗДЕЛА: {section_task}

РЕЛЕВАНТНЫЕ ДАННЫЕ ДЛЯ ЭТОГО РАЗДЕЛА:
- Факты: {relevant_facts}
- Важные аспекты: {relevant_truths}
- Советы: {relevant_advice}

ПРЕДЫДУЩИЕ РАЗДЕЛЫ (НЕ ПОВТОРЯЙ):
{chr(10).join(previous[-2:]) if previous else "Первый раздел"}

ТРЕБОВАНИЯ:
- 600-800 символов полезной информации
- Конкретные примеры из жизни
- Естественный язык, ориентированный на обычных людей
- Только новая информация, не повторяющая предыдущие разделы

Напиши текст этого раздела.
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": f"Ты адаптируешь психологические темы для обычных людей. Пиши конкретно и полезно."
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=800,
                temperature=0.7
            )
            
            content = response.choices[0].message.content.strip()
            return content if len(content) > 300 else ""
            
        except Exception as e:
            logging.error(f"Ошибка генерации раздела: {e}")
            return ""

    def _select_relevant_facts(self, facts: List[str], section_task: str) -> str:
        """Выбирает релевантные факты для раздела"""
        relevant = []
        task_lower = section_task.lower()
        
        for fact in facts:
            fact_lower = fact.lower()
            # Подбираем факты по ключевым словам
            if any(keyword in task_lower for keyword in ['статистик', 'данн', 'исследован', 'научн']):
                if any(word in fact_lower for word in ['%', 'исследовани', 'учен', 'доказа']):
                    relevant.append(fact)
            elif any(keyword in task_lower for keyword in ['практик', 'техник', 'метод', 'упражнен']):
                if any(word in fact_lower for word in ['техник', 'метод', 'упражнен', 'практик']):
                    relevant.append(fact)
            else:
                relevant.append(fact)  # Для общих разделов берем все
        
        return ", ".join(relevant[:2])  # Ограничиваем количеством

    def _select_relevant_truths(self, truths: List[str], section_task: str) -> str:
        """Выбирает релевантные скрытые аспекты"""
        relevant = []
        task_lower = section_task.lower()
        
        for truth in truths:
            truth_lower = truth.lower()
            # Подбираем по контексту раздела
            if any(keyword in task_lower for keyword in ['причин', 'механизм', 'глубинн', 'анализ']):
                if any(word in truth_lower for word in ['причин', 'механизм', 'скрыт', 'на самом']):
                    relevant.append(truth)
            elif any(keyword in task_lower for keyword in ['решен', 'совет', 'помощь']):
                if any(word in truth_lower for word in ['важн', 'нужн', 'следу']):
                    relevant.append(truth)
        
        return ", ".join(relevant[:2])

    def _select_relevant_advice(self, advice: List[str], section_task: str) -> str:
        """Выбирает релевантные советы"""
        task_lower = section_task.lower()
        
        if any(keyword in task_lower for keyword in ['практик', 'техник', 'метод', 'упражнен', 'решен']):
            return ", ".join(advice)  # Для практических разделов берем все советы
        else:
            return ", ".join(advice[:1])  # Для других - ограничиваем

    def _build_theme_article(self, theme: str, sections: List[str], analysis: Dict) -> str:
        """Сборка статьи для конкретной темы"""
        content_parts = [theme, ""]
        
        for i, section in enumerate(sections):
            # Простые заголовки для разделов
            header = f"ЧАСТЬ {i+1}"
            content_parts.extend([header, section, ""])
        
        return "\n".join(content_parts)

    def _is_unique_content(self, content: str, previous: List[str]) -> bool:
        """Проверяет уникальность контента"""
        if not previous:
            return True
        
        # Простая проверка на дублирование
        content_words = set(content.lower().split())
        for prev in previous[-2:]:  # Проверяем только последние 2 раздела
            prev_words = set(prev.lower().split())
            overlap = len(content_words & prev_words)
            if overlap > len(content_words) * 0.3:  # Если больше 30% совпадений
                return False
        
        return True

    def _generate_article_plan(self, analysis: Dict) -> Optional[Dict]:
        """Генерация плана статьи"""
        try:
            plan_prompt = f"""
Создай детальный план для статьи на тему: "{analysis['main_theme']}"

Основная идея: {analysis['main_message']}

Требования к плану:
- 4-5 основных разделов
- Каждый раздел должен быть самодостаточным
- План должен позволять писать каждый раздел отдельно
- Учитывай лимит в 3000 символов на раздел

Формат ответа (только названия разделов):
1. Введение и актуальность темы
2. [Название второго раздела]
3. [Название третьего раздела]
4. [Название четвертого раздела] 
5. Заключение и основные выводы
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты создаешь структурированные планы для статей."
                    },
                    {"role": "user", "content": plan_prompt}
                ],
                max_tokens=800,
                temperature=0.7
            )
            
            plan_text = response.choices[0].message.content.strip()
            
            # Извлекаем разделы из плана
            sections = []
            for line in plan_text.split('\n'):
                line = line.strip()
                if re.match(r'^\d+\.', line):
                    section_title = re.sub(r'^\d+\.\s*', '', line)
                    sections.append(section_title)
            
            return {'sections': sections[:5]}  # Ограничиваем 5 разделами
            
        except Exception as e:
            logging.error(f"Ошибка генерации плана: {e}")
            return None

    def _generate_section(self, analysis: Dict, section_title: str, section_index: int) -> str:
        """Генерация одного раздела"""
        try:
            section_prompt = f"""
Напиши раздел статьи на тему: "{analysis['main_theme']}"

РАЗДЕЛ: {section_title}
ОСНОВНАЯ ИДЕЯ СТАТЬИ: {analysis['main_message']}

ТРЕБОВАНИЯ К РАЗДЕЛУ:
- Объем: 800-1200 символов
- Самодостаточный текст
- Конкретные примеры и факты
- Практические советы если уместно
- Естественный переход к следующему разделу

ДОПОЛНИТЕЛЬНАЯ ИНФОРМАЦИЯ:
{analysis['interesting_facts']}
{analysis['hidden_truths']}

Пиши плотный, информативный текст. Не используй разметку.
"""

            response = self.client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты пишешь информативные разделы статей. Каждый раздел должен быть ценным сам по себе."
                    },
                    {"role": "user", "content": section_prompt}
                ],
                max_tokens=1200,  # ~900 символов
                temperature=0.8
            )
            
            return response.choices[0].message.content.strip()
            
        except Exception as e:
            logging.error(f"Ошибка генерации раздела {section_title}: {e}")
            return ""

    def _combine_sections(self, analysis: Dict, sections: List[str]) -> str:
        """Объединение разделов в единую статью"""
        if not sections:
            return ""
        
        # Создаем заголовок
        title = analysis['main_theme']
        
        # Объединяем разделы
        full_content = f"{title}\n\n"
        
        for i, section in enumerate(sections):
            if i == 0:
                full_content += f"ВВЕДЕНИЕ\n{section}\n\n"
            elif i == len(sections) - 1:
                full_content += f"ЗАКЛЮЧЕНИЕ\n{section}\n\n"
            else:
                full_content += f"РАЗДЕЛ {i}\n{section}\n\n"
        
        return full_content

    def _process_final_article(self, content: str, analysis: Dict, length: int) -> Dict:
        """Финальная обработка статьи"""
        try:
            # Преобразуем в HTML
            html_content = self._convert_to_simple_html(content)
            
            title = self._extract_title_from_text(content)
            
            return {
                'title': title,
                'content': html_content,
                'excerpt': self._generate_excerpt(content),
                'meta_title': title,
                'meta_description': self._generate_meta_description(content),
                'category': self._determine_category(analysis),
                'tags': self._generate_tags(analysis, content),
                'faq': [],
                'word_count': length,
                'original_analysis': analysis
            }
            
        except Exception as e:
            logging.error(f"Ошибка финальной обработки: {e}")
            return None

    def _convert_to_simple_html(self, content: str) -> str:
        """Простое преобразование в HTML"""
        lines = content.split('\n')
        html_parts = []
        
        i = 0
        while i < len(lines):
            line = lines[i].strip()
            if not line:
                i += 1
                continue
                
            # Заголовок статьи
            if i == 0:
                html_parts.append(f'<h1>{line}</h1>')
                i += 1
                continue
                
            # Заголовки разделов (прописные)
            if line.isupper() and len(line) < 100:
                if 'ВВЕДЕНИЕ' in line or 'ЧАСТЬ 1:' in line:
                    html_parts.append('<h2>Введение</h2>')
                elif 'ЗАКЛЮЧЕНИЕ' in line or 'ВЫВОДЫ' in line or 'ЧАСТЬ 4:' in line:
                    html_parts.append('<h2>Заключение</h2>')
                elif 'АНАЛИЗ' in line or 'ЧАСТЬ 2:' in line:
                    html_parts.append('<h2>Анализ</h2>')
                elif 'РЕШЕНИЯ' in line or 'ЧАСТЬ 3:' in line:
                    html_parts.append('<h2>Решения</h2>')
                elif line.startswith('РАЗДЕЛ'):
                    html_parts.append(f'<h2>Часть {line.replace("РАЗДЕЛ", "").strip()}</h2>')
                else:
                    html_parts.append(f'<h2>{line.title()}</h2>')
                i += 1
                continue
                
            # Обычный текст
            if line and not line.isupper():
                html_parts.append(f'<p>{line}</p>')
                
            i += 1
        
        return '\n'.join(html_parts)

    def _build_writing_prompt(self, analysis: Dict) -> str:
        """Финальный промпт с четкими указаниями"""
        return f"""
НАПИШИ ДЛИННЮЮ СТАТЬЮ НА ТЕМУ: "{analysis['main_theme']}"

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
        
        # Если первая строка это "ЧАСТЬ 1: ВВЕДЕНИЕ", то пытаемся извлечь тему из нее
        if "ЧАСТЬ 1:" in first_line.upper():
            # Ищем реальную тему в содержании
            lines = content.split('\n')
            for line in lines:
                line = line.strip()
                # Пропускаем служебные строки
                if not line or line.upper().startswith("ЧАСТЬ"):
                    continue
                # Берем первую содержательную строку как тему
                if len(line) > 10 and len(line) < 100:
                    return line
        
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
    article = writer.write_adapted_article_quality(test_analysis)
    
    if article:
        print("Статья написана:")
        print(f"Заголовок: {article['title']}")
        print(f"Категория: {article['category']}")
        print(f"Теги: {', '.join(article['tags'])}")
        print(f"Слов: {article['word_count']}")
    else:
        print("Ошибка написания статьи")