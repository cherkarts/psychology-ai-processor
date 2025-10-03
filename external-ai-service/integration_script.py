#!/usr/bin/env python3
"""
Скрипт для интеграции сгенерированных статей с сайтом
"""

import json
import os
import requests
import logging
from datetime import datetime
from typing import List, Dict, Optional

class SiteIntegration:
    def __init__(self, site_url: str, admin_api_key: str = None):
        self.site_url = site_url.rstrip('/')
        self.admin_api_key = admin_api_key
        
    def upload_article_to_site(self, article: Dict) -> bool:
        """Загрузить статью на сайт через API"""
        try:
            # Подготавливаем данные для API
            article_data = {
                'title': article['title'],
                'content': article['content'],
                'excerpt': article['excerpt'],
                'meta_title': article['meta_title'],
                'meta_description': article['meta_description'],
                'tags': ','.join(article['tags']),
                'category_id': self._get_category_id(article),
                'image': article.get('featured_image', ''),  # Используем 'image' вместо 'featured_image'
                'slug': self._generate_slug(article['title']),  # Добавляем генерацию slug
                'is_active': 1,
                'author': 'Денис Черкас',  # Устанавливаем автора
                'source': article.get('source', 'Psychology Today'),
                'processing_date': article.get('processing_date', datetime.now().strftime('%Y-%m-%d'))
            }
            
            # Отправляем на сайт
            response = requests.post(
                f"{self.site_url}/admin/api/save-article.php",
                json=article_data,
                headers={
                    'Content-Type': 'application/json',
                    'Authorization': f'Bearer {self.admin_api_key}' if self.admin_api_key else None
                },
                timeout=30
            )
            
            if response.status_code == 200:
                result = response.json()
                if result.get('success'):
                    logging.info(f"✅ Статья загружена: {article['title'][:50]}...")
                    return True
                else:
                    logging.error(f"❌ Ошибка API: {result.get('message', 'Unknown error')}")
            else:
                logging.error(f"❌ HTTP ошибка: {response.status_code}")
                
        except Exception as e:
            logging.error(f"❌ Ошибка загрузки статьи: {e}")
            
        return False
    
    def _get_category_id(self, article: Dict) -> int:
        """Получить ID категории из статьи или по названию"""
        # Если в статье уже есть category_id, используем его
        if 'category_id' in article and article['category_id']:
            return article['category_id']
        
        # Иначе используем маппинг по названию
        category_name = article.get('category', 'Психология')
        category_mapping = {
            'Психология': 74,
            'Отношения': 1,
            'Стресс и тревога': 2,
            'Детская психология': 3,
            'Саморазвитие': 4,
            'Карьера и успех': 5,
            'Психическое здоровье': 6
        }
        return category_mapping.get(category_name, 74)  # По умолчанию "Психология" (ID 74)
    
    def _generate_slug(self, title: str) -> str:
        """Генерировать красивый slug из заголовка"""
        import re
        
        # Транслитерация русских букв
        translit_map = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
            'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
            'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
            'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
            'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
        }
        
        # Приводим к нижнему регистру
        slug = title.lower()
        
        # Заменяем русские буквы на латинские
        for ru, en in translit_map.items():
            slug = slug.replace(ru, en)
        
        # Убираем все кроме букв, цифр, дефисов и подчеркиваний
        slug = re.sub(r'[^a-z0-9\-_]', '-', slug)
        
        # Убираем множественные дефисы
        slug = re.sub(r'-+', '-', slug)
        
        # Убираем дефисы в начале и конце
        slug = slug.strip('-')
        
        # Ограничиваем длину
        if len(slug) > 50:
            slug = slug[:50].rstrip('-')
        
        return slug
    
    def upload_articles_from_file(self, json_file: str) -> Dict:
        """Загрузить статьи из JSON файла"""
        try:
            with open(json_file, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            articles = data.get('articles', [])
            results = {
                'total': len(articles),
                'success': 0,
                'failed': 0,
                'errors': []
            }
            
            for article in articles:
                if self.upload_article_to_site(article):
                    results['success'] += 1
                else:
                    results['failed'] += 1
                    results['errors'].append(article['title'])
            
            return results
            
        except Exception as e:
            logging.error(f"❌ Ошибка чтения файла: {e}")
            return {'total': 0, 'success': 0, 'failed': 0, 'errors': [str(e)]}
    
    def check_site_connection(self) -> bool:
        """Проверить соединение с сайтом"""
        try:
            response = requests.get(f"{self.site_url}/", timeout=10)
            return response.status_code == 200
        except:
            return False

def main():
    """Основная функция для интеграции"""
    logging.basicConfig(level=logging.INFO)
    
    # Настройки
    site_url = os.getenv('SITE_URL', 'https://cherkas-therapy.ru')
    admin_api_key = os.getenv('ADMIN_API_KEY')
    
    # Ищем последний сгенерированный файл
    json_files = [f for f in os.listdir('.') if f.startswith('psychology_articles_') and f.endswith('.json')]
    
    if not json_files:
        logging.error("❌ Не найдены файлы со статьями")
        return
    
    # Берем самый новый файл
    latest_file = max(json_files, key=os.path.getctime)
    logging.info(f"📁 Обрабатываем файл: {latest_file}")
    
    # Создаем интегратор
    integrator = SiteIntegration(site_url, admin_api_key)
    
    # Проверяем соединение
    if not integrator.check_site_connection():
        logging.error(f"❌ Не удается подключиться к сайту: {site_url}")
        return
    
    # Загружаем статьи
    results = integrator.upload_articles_from_file(latest_file)
    
    # Выводим результаты
    logging.info("📊 РЕЗУЛЬТАТЫ ЗАГРУЗКИ:")
    logging.info(f"   Всего статей: {results['total']}")
    logging.info(f"   Успешно загружено: {results['success']}")
    logging.info(f"   Ошибок: {results['failed']}")
    
    if results['errors']:
        logging.info("❌ Ошибки:")
        for error in results['errors']:
            logging.info(f"   - {error}")

if __name__ == "__main__":
    main()
