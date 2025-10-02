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
                        "content": """Ты опытный психолог и писатель, специализирующийся на создании адаптированного контента для российской и белорусской аудитории. 

Твои принципы:
- Пиши с эмпатией и пониманием
- Используй живые примеры из российской/белорусской жизни
- Избегай западных клише и шаблонов
- Говори о том, о чем обычно молчат психологи
- Давай практические, выполнимые советы
- Создавай статьи, которые действительно помогают людям"""
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=4000 if model_to_use == "gpt-4" else 3500,
                temperature=0.8,
                top_p=0.95
            )
            
            article_content = response.choices[0].message.content.strip()
            return self._process_article_content(article_content, analysis)
            
        except Exception as e:
            logging.error(f"Ошибка при написании статьи: {e}")
            return None
    
    def _build_writing_prompt(self, analysis: Dict) -> str:
        """Построить промпт для написания статьи"""
        return f"""
Напиши психологическую статью для российского и белорусского читателя на основе этого анализа:

**ОРИГИНАЛЬНАЯ ТЕМА:** {analysis['main_theme']}
**СЮЖЕТ:** {analysis['core_narrative']}
**ПОСЫЛ:** {analysis['main_message']}
**ТОН:** {analysis['emotional_tone']}

**ИНТЕРЕСНЫЕ ФАКТЫ:**
{chr(10).join(f"- {fact}" for fact in analysis['interesting_facts'])}

**ТО, О ЧЕМ МОЛЧАТ ПСИХОЛОГИ:**
{chr(10).join(f"- {truth}" for truth in analysis['hidden_truths'])}

**ПРАКТИЧЕСКИЕ СОВЕТЫ:**
{chr(10).join(f"- {advice}" for advice in analysis['practical_advice'])}

**КУЛЬТУРНАЯ АДАПТАЦИЯ:**
{analysis['cultural_adaptation_notes']}

**СТРУКТУРА СТАТЬИ:**
- Введение: {analysis['article_structure']['introduction_approach']}
- Проблема: {analysis['article_structure']['problem_presentation']}
- Решение: {analysis['article_structure']['solution_approach']}
- Заключение: {analysis['article_structure']['conclusion_style']}

**ТРЕБОВАНИЯ К СТАТЬЕ:**

🎯 **ОБЪЕМ:** 12,000-18,000 символов (без пробелов)
🎯 **СТРУКТУРА:** HTML с заголовками h1, h2, h3, параграфами p, списками ul/li
🎯 **СТИЛЬ:** Живой, эмпатичный, без канцелярита

📖 **СОДЕРЖАНИЕ:**
- Начни с эмоционального крючка (реальная история из российской жизни)
- Раскрой проблему через призму российского менталитета
- Покажи, что это нормально и с этим можно справиться
- Дай конкретные, выполнимые советы
- Включи то, о чем обычно молчат психологи
- Заверши мотивирующим призывом к действию

🇷🇺 **РОССИЙСКИЙ КОНТЕКСТ:**
- Используй примеры из российской/белорусской жизни
- Учитывай особенности менталитета (семейные ценности, отношение к психологии)
- Говори о том, что действительно волнует людей в СНГ
- Избегай западных шаблонов и клише

💡 **ПРАКТИЧНОСТЬ:**
- Каждый совет должен быть выполнимым
- Давай конкретные техники и упражнения
- Объясняй "почему это работает"
- Предупреждай о возможных трудностях

⚡ **ЭМОЦИОНАЛЬНОСТЬ:**
- Вызывай сопереживание и понимание
- Показывай, что читатель не одинок в своих проблемах
- Давай надежду и веру в возможность изменений
- Используй метафоры и образы, понятные россиянам

**ФОРМАТ ОТВЕТА:**
Начни сразу с HTML-разметки. Структура:

<h1>Цепляющий заголовок</h1>
<p>Эмоциональный крючок - реальная история</p>

<h2>Проблема глазами россиянина</h2>
<p>Описание проблемы через российский контекст</p>

<h2>Что на самом деле происходит</h2>
<p>Анализ механизмов и причин</p>

<h2>То, о чем молчат психологи</h2>
<p>Скрытые истины и неудобные факты</p>

<h2>Практические техники</h2>
<p>Конкретные советы и упражнения</p>

<h2>Заключение</h2>
<p>Мотивирующий призыв к действию</p>

**ВАЖНО:** Статья должна быть полезна, интересна и запоминающейся!
"""
    
    def _process_article_content(self, content: str, analysis: Dict) -> Dict:
        """Обработать написанную статью"""
        try:
            # Извлекаем заголовок
            title = self._extract_title(content)
            
            # Очищаем контент
            cleaned_content = self._clean_html_content(content)
            
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
            
            return {
                'title': title,
                'content': cleaned_content,
                'excerpt': excerpt,
                'meta_title': meta_title,
                'meta_description': meta_description,
                'category': category,
                'tags': tags,
                'faq': faq,
                'word_count': len(cleaned_content.replace(' ', '')),
                'original_analysis': analysis
            }
            
        except Exception as e:
            logging.error(f"Ошибка при обработке контента статьи: {e}")
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
        # Убираем лишние пробелы и переносы
        content = re.sub(r'\n\s*\n', '\n', content)
        content = re.sub(r' +', ' ', content)
        
        # Убираем пустые параграфы
        content = re.sub(r'<p>\s*</p>', '', content)
        
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
