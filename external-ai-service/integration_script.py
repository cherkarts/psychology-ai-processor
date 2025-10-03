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
                'category_id': self._get_category_id(article['category']),
                'featured_image': article.get('featured_image', ''),
                'is_active': 1,
                'author': 'AI Assistant',
                'source': article.get('source', 'Psychology Today'),
                'processing_date': article.get('processing_date', datetime.now().strftime('%Y-%m-%d'))
            }
            
            # Отправляем на сайт
            response = requests.post(
                f"{self.site_url}/api/ai-save-article.php",
                json=article_data,
                headers={
                    'Content-Type': 'application/json',
                    'Authorization': f'Bearer {self.admin_api_key}' if self.admin_api_key else 'Bearer default-ai-key'
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
    
    def _get_category_id(self, category_name: str) -> int:
        """Получить ID категории по названию"""
        category_mapping = {
            'Психология': 1,
            'Саморазвитие': 2,
            'Отношения': 3,
            'Стресс и тревога': 4,
            'Детская психология': 5,
            'Семейная терапия': 6
        }
        return category_mapping.get(category_name, 1)  # По умолчанию "Психология"
    
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
