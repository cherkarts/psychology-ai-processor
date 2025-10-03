#!/usr/bin/env python3
"""
Модуль для анализа статей с Psychology Today дешевой AI моделью
"""

import json
import logging
from typing import Dict, List, Optional
from openai import OpenAI
import os
from dotenv import load_dotenv

load_dotenv()

class ContentAnalyzer:
    def __init__(self):
        self.client = OpenAI(
            api_key=os.getenv('OPENAI_API_KEY'),
            timeout=60
        )
        self.analysis_model = "gpt-3.5-turbo"  # Дешевая модель для анализа
        
    def analyze_article(self, article: Dict) -> Optional[Dict]:
        """Анализировать статью и выделить ключевые элементы"""
        try:
            prompt = self._build_analysis_prompt(article)
            
            response = self.client.chat.completions.create(
                model=self.analysis_model,
                messages=[
                    {
                        "role": "system", 
                        "content": "Ты эксперт-аналитик психологического контента. Твоя задача - выделить ключевые элементы статьи для последующей адаптации под российскую аудиторию."
                    },
                    {"role": "user", "content": prompt}
                ],
                max_tokens=800,
                temperature=0.3
            )
            
            analysis_text = response.choices[0].message.content.strip()
            return self._parse_analysis(analysis_text, article)
            
        except Exception as e:
            logging.error(f"Ошибка при анализе статьи: {e}")
            return None
    
    def _build_analysis_prompt(self, article: Dict) -> str:
        """Построить промпт для анализа статьи"""
        return f"""
Проанализируй эту статью с Psychology Today и выдели ключевые элементы для адаптации под российскую аудиторию:

**ЗАГОЛОВОК:** {article['title']}

**СОДЕРЖАНИЕ:**
{article['content'][:3000]}...

**ЗАДАЧА АНАЛИЗА:**
Выдели и структурируй следующую информацию в формате JSON:

{{
    "main_theme": "Основная тема (1-2 слова)",
    "core_narrative": "Ключевой сюжет/нарратив (3-4 предложения)",
    "main_message": "Главный посыл/вывод автора",
    "interesting_facts": [
        "Интересный факт 1",
        "Интересный факт 2", 
        "Интересный факт 3"
    ],
    "hidden_truths": [
        "То, о чем обычно молчат психологи 1",
        "То, о чем обычно молчат психологи 2"
    ],
    "practical_advice": [
        "Практический совет 1",
        "Практический совет 2",
        "Практический совет 3"
    ],
    "emotional_tone": "Эмоциональный тон (поддерживающий/провокационный/научный)",
    "target_audience": "Целевая аудитория",
    "cultural_adaptation_notes": "Особенности для адаптации под российскую аудиторию",
    "article_structure": {{
        "introduction_approach": "Как лучше начать статью",
        "problem_presentation": "Как представить проблему",
        "solution_approach": "Как подать решение",
        "conclusion_style": "Стиль заключения"
    }},
    "local_examples_needed": [
        "Пример 1 для российского контекста",
        "Пример 2 для российского контекста"
    ],
    "sensitivity_notes": "Особенности, которые нужно учесть для российской аудитории"
}}

**ВАЖНО:**
- Отвечай ТОЛЬКО в формате JSON
- Будь конкретным и практичным
- Учитывай культурные особенности
- Выделяй то, что действительно важно для читателя
"""
    
    def _parse_analysis(self, analysis_text: str, original_article: Dict) -> Dict:
        """Парсить результат анализа"""
        try:
            # Пытаемся извлечь JSON из ответа
            json_start = analysis_text.find('{')
            json_end = analysis_text.rfind('}') + 1
            
            if json_start != -1 and json_end > json_start:
                json_str = analysis_text[json_start:json_end]
                analysis = json.loads(json_str)
            else:
                # Если JSON не найден, создаем базовую структуру
                analysis = self._create_fallback_analysis(analysis_text)
            
            # Добавляем метаданные
            analysis['original_url'] = original_article['url']
            analysis['original_title'] = original_article['title']
            analysis['original_author'] = original_article.get('author', 'Unknown')
            analysis['word_count'] = original_article.get('word_count', 0)
            
            return analysis
            
        except json.JSONDecodeError as e:
            logging.error(f"Ошибка парсинга JSON анализа: {e}")
            return self._create_fallback_analysis(analysis_text)
    
    def _create_fallback_analysis(self, analysis_text: str) -> Dict:
        """Создать базовый анализ, если JSON не удалось распарсить"""
        return {
            "main_theme": "Психология",
            "core_narrative": "Статья о психологических аспектах жизни человека",
            "main_message": "Важность понимания психологических процессов",
            "interesting_facts": ["Психология влияет на все аспекты жизни"],
            "hidden_truths": ["Многие психологические проблемы решаемы"],
            "practical_advice": ["Обратитесь к специалисту", "Практикуйте самопомощь"],
            "emotional_tone": "поддерживающий",
            "target_audience": "Общая аудитория",
            "cultural_adaptation_notes": "Адаптировать под российский менталитет",
            "article_structure": {
                "introduction_approach": "Начать с интересного факта",
                "problem_presentation": "Описать проблему доступно",
                "solution_approach": "Предложить практические решения",
                "conclusion_style": "Мотивирующее заключение"
            },
            "local_examples_needed": ["Российские примеры"],
            "sensitivity_notes": "Учесть особенности российской культуры"
        }
    
    def analyze_multiple_articles(self, articles: List[Dict]) -> List[Dict]:
        """Анализировать несколько статей"""
        analyses = []
        
        for i, article in enumerate(articles, 1):
            logging.info(f"Анализирую статью {i}/{len(articles)}: {article['title'][:50]}...")
            
            analysis = self.analyze_article(article)
            if analysis:
                analyses.append(analysis)
            else:
                logging.warning(f"Не удалось проанализировать статью: {article['title']}")
        
        logging.info(f"Успешно проанализировано {len(analyses)} из {len(articles)} статей")
        return analyses

if __name__ == "__main__":
    # Тестирование анализатора
    logging.basicConfig(level=logging.INFO)
    
    # Пример статьи для тестирования
    test_article = {
        'title': 'How to Deal with Anxiety in Relationships',
        'content': 'Anxiety in relationships is a common issue that affects many people...',
        'author': 'Dr. Smith',
        'url': 'https://example.com/article1'
    }
    
    analyzer = ContentAnalyzer()
    analysis = analyzer.analyze_article(test_article)
    
    if analysis:
        print("Анализ статьи:")
        print(json.dumps(analysis, ensure_ascii=False, indent=2))
    else:
        print("Ошибка анализа")
