#!/usr/bin/env python3
"""
Модуль для подбора изображений к статьям
"""

import requests
import logging
import os
from typing import Dict, List, Optional
from dotenv import load_dotenv
import time

load_dotenv()

class ImagePrompter:
    def __init__(self):
        self.unsplash_access_key = os.getenv('UNSPLASH_ACCESS_KEY')
        self.openai_client = None
        
        # Инициализация OpenAI для генерации промптов
        if os.getenv('OPENAI_API_KEY'):
            from openai import OpenAI
            self.openai_client = OpenAI(api_key=os.getenv('OPENAI_API_KEY'))
    
    def generate_image_prompt(self, article: Dict) -> str:
        """Генерировать промпт для создания изображения"""
        if not self.openai_client:
            return self._create_fallback_prompt(article)
        
        try:
            prompt = f"""
Создай промпт для генерации изображения к психологической статье:

**ЗАГОЛОВОК:** {article['title']}
**ТЕМА:** {article.get('original_analysis', {}).get('main_theme', 'психология')}
**ТОН:** {article.get('original_analysis', {}).get('emotional_tone', 'поддерживающий')}
**КАТЕГОРИЯ:** {article['category']}

**ТРЕБОВАНИЯ К ИЗОБРАЖЕНИЮ:**
- Соответствует эмоциональному тону статьи
- Культурно релевантно для России/Беларуси
- Абстрактное/метафорическое, а не буквальное
- Высокое качество, психологическая глубина
- Без конкретных лиц (если не необходимо)
- Подходит для психологического контента

**ФОРМАТ ОТВЕТА:**
Дай только промпт для генерации изображения на английском языке (для Unsplash API).
Промпт должен быть конкретным, но не слишком длинным (до 10 слов).

Пример: "calm meditation nature therapy"
"""
            
            response = self.openai_client.chat.completions.create(
                model="gpt-3.5-turbo",
                messages=[
                    {"role": "system", "content": "Ты эксперт по созданию промптов для изображений. Создавай краткие, точные промпты на английском языке."},
                    {"role": "user", "content": prompt}
                ],
                max_tokens=50,
                temperature=0.7
            )
            
            image_prompt = response.choices[0].message.content.strip()
            return image_prompt
            
        except Exception as e:
            logging.error(f"Ошибка при генерации промпта изображения: {e}")
            return self._create_fallback_prompt(article)
    
    def _create_fallback_prompt(self, article: Dict) -> str:
        """Создать базовый промпт, если AI недоступен"""
        category = article.get('category', '').lower()
        theme = article.get('original_analysis', {}).get('main_theme', '').lower()
        
        # Маппинг категорий на промпты
        category_prompts = {
            'отношения': 'relationships therapy counseling',
            'стресс и тревога': 'anxiety stress relief calm',
            'детская психология': 'child psychology family therapy',
            'саморазвитие': 'personal growth motivation success',
            'психология': 'psychology therapy mental health'
        }
        
        # Маппинг тем на промпты
        theme_prompts = {
            'тревога': 'anxiety calm meditation',
            'депрессия': 'depression hope therapy',
            'отношения': 'relationships love counseling',
            'семья': 'family therapy parenting',
            'стресс': 'stress relief relaxation'
        }
        
        # Ищем подходящий промпт
        for key, prompt in theme_prompts.items():
            if key in theme:
                return prompt
        
        for key, prompt in category_prompts.items():
            if key in category:
                return prompt
        
        return 'psychology therapy mental health'
    
    def search_unsplash_image(self, image_prompt: str) -> Optional[str]:
        """Поиск изображения на Unsplash"""
        if not self.unsplash_access_key:
            logging.warning("Unsplash API ключ не настроен")
            return None
        
        try:
            # Формируем поисковый запрос
            search_query = image_prompt.replace(' ', '+')
            
            url = "https://api.unsplash.com/search/photos"
            params = {
                'query': search_query,
                'per_page': 1,
                'page': 1,
                'orientation': 'landscape',
                'content_filter': 'high'  # Высокое качество
            }
            headers = {
                'Authorization': f'Client-ID {self.unsplash_access_key}'
            }
            
            response = requests.get(url, params=params, headers=headers, timeout=10)
            
            if response.status_code == 200:
                data = response.json()
                if data['results']:
                    image_url = data['results'][0]['urls']['regular']
                    logging.info(f"Найдено изображение: {image_url}")
                    return image_url
                else:
                    logging.warning(f"Не найдено изображений для запроса: {image_prompt}")
                    return None
            else:
                logging.error(f"Ошибка Unsplash API: {response.status_code}")
                return None
                
        except Exception as e:
            logging.error(f"Ошибка при поиске изображения: {e}")
            return None
    
    def get_image_for_article(self, article: Dict) -> Optional[str]:
        """Получить изображение для статьи"""
        try:
            # Генерируем промпт
            image_prompt = self.generate_image_prompt(article)
            logging.info(f"Промпт для изображения: {image_prompt}")
            
            # Ищем изображение
            image_url = self.search_unsplash_image(image_prompt)
            
            if image_url:
                return image_url
            else:
                # Пробуем альтернативные промпты
                alternative_prompts = self._get_alternative_prompts(article)
                
                for alt_prompt in alternative_prompts:
                    image_url = self.search_unsplash_image(alt_prompt)
                    if image_url:
                        return image_url
                    time.sleep(1)  # Пауза между запросами
                
                logging.warning(f"Не удалось найти изображение для статьи: {article['title']}")
                return None
                
        except Exception as e:
            logging.error(f"Ошибка при получении изображения: {e}")
            return None
    
    def _get_alternative_prompts(self, article: Dict) -> List[str]:
        """Получить альтернативные промпты для поиска изображения"""
        category = article.get('category', '').lower()
        
        alternatives = {
            'отношения': ['couple therapy', 'relationship counseling', 'love support'],
            'стресс и тревога': ['stress relief', 'anxiety help', 'calm therapy'],
            'детская психология': ['child therapy', 'family counseling', 'parenting support'],
            'саморазвитие': ['personal growth', 'self improvement', 'motivation therapy'],
            'психология': ['psychology therapy', 'mental health', 'counseling support']
        }
        
        return alternatives.get(category, ['psychology therapy', 'mental health support'])
    
    def get_images_for_articles(self, articles: List[Dict]) -> List[Dict]:
        """Получить изображения для нескольких статей"""
        articles_with_images = []
        
        for i, article in enumerate(articles, 1):
            logging.info(f"Ищу изображение для статьи {i}/{len(articles)}: {article['title'][:50]}...")
            
            image_url = self.get_image_for_article(article)
            if image_url:
                article['featured_image'] = image_url
                articles_with_images.append(article)
            else:
                # Добавляем статью без изображения
                article['featured_image'] = None
                articles_with_images.append(article)
            
            time.sleep(2)  # Пауза между запросами к API
        
        logging.info(f"Изображения найдены для {len([a for a in articles_with_images if a.get('featured_image')])} из {len(articles)} статей")
        return articles_with_images

if __name__ == "__main__":
    # Тестирование промптера изображений
    logging.basicConfig(level=logging.INFO)
    
    # Пример статьи для тестирования
    test_article = {
        'title': 'Как справиться с тревогой в отношениях',
        'category': 'Отношения',
        'original_analysis': {
            'main_theme': 'тревога в отношениях',
            'emotional_tone': 'поддерживающий'
        }
    }
    
    prompter = ImagePrompter()
    image_url = prompter.get_image_for_article(test_article)
    
    if image_url:
        print(f"Найдено изображение: {image_url}")
    else:
        print("Изображение не найдено")
