#!/usr/bin/env python3
"""
Простой цикл публикации готовых демо статей
"""

import os
import json
import requests
import re
from datetime import datetime

def load_demo_articles():
    """Загрузка демо статей"""
    print("📂 Загрузка демо статей...")
    
    # Ищем демо файл
    demo_files = [f for f in os.listdir('.') if f.startswith('psychology_articles_demo_') and f.endswith('.json')]
    
    if not demo_files:
        print("❌ Демо файлы не найдены")
        return None
    
    demo_file = demo_files[0]
    print(f"Используем файл: {demo_file}")
    
    try:
        with open(demo_file, 'r', encoding='utf-8') as f:
            demo_data = json.load(f)
        
        if isinstance(demo_data, dict) and 'articles' in demo_data:
            articles = demo_data['articles']
        elif isinstance(demo_data, list):
            articles = demo_data
        else:
            print("❌ Неверный формат файла")
            return None
        
        print(f"✅ Загружено {len(articles)} статей")
        return articles
        
    except Exception as e:
        print(f"❌ Ошибка загрузки файла: {e}")
        return None

def publish_article(article, article_num, total):
    """Публикация одной статьи"""
    print(f"\n📝 Публикация статьи {article_num}/{total}: {article.get('title', 'Без названия')}")
    
    try:
        # Подготавливаем данные
        article_data = {
            "title": article.get('title', 'Статья без названия'),
            "content": article.get('content', ''),
            "excerpt": article.get('excerpt', ''),
            "meta_title": article.get('title', ''),
            "meta_description": article.get('excerpt', ''),
            "tags": article.get('tags', []),
            "category_id": 1,  # Психология
            "author": article.get('author', 'Денис Черкас'),
            "is_active": 1
        }
        
        # Отправляем на сайт
        response = requests.post(
            "https://cherkas-therapy.ru/upload_article_smart.php",
            json=article_data,
            headers={'Content-Type': 'application/json'},
            timeout=30
        )
        
        if response.status_code == 200:
            response_text = response.text
            
            if "Статья успешно добавлена!" in response_text:
                # Извлекаем ID статьи
                id_match = re.search(r'<strong>ID статьи:</strong> (\d+)', response_text)
                if id_match:
                    article_id = id_match.group(1)
                    print(f"✅ Статья опубликована (ID: {article_id})")
                    return True
                else:
                    print("✅ Статья опубликована")
                    return True
            else:
                print("❌ Ошибка публикации")
                return False
        else:
            print(f"❌ HTTP ошибка: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

def main():
    """Основная функция"""
    print("🚀 ПРОСТОЙ ЦИКЛ ПУБЛИКАЦИИ ДЕМО СТАТЕЙ")
    print("=" * 50)
    
    # Загружаем демо статьи
    articles = load_demo_articles()
    
    if not articles:
        print("❌ Не удалось загрузить демо статьи")
        return False
    
    # Публикуем статьи
    print(f"\n📝 Публикация {len(articles)} статей...")
    
    published_count = 0
    
    for i, article in enumerate(articles, 1):
        if publish_article(article, i, len(articles)):
            published_count += 1
    
    # Итоговый отчет
    print("\n" + "=" * 50)
    print("📊 ИТОГОВЫЙ ОТЧЕТ")
    print("=" * 50)
    
    print(f"📝 Всего статей: {len(articles)}")
    print(f"✅ Опубликовано: {published_count}")
    print(f"❌ Ошибок: {len(articles) - published_count}")
    
    if published_count == len(articles):
        print("🎉 ВСЕ СТАТЬИ УСПЕШНО ОПУБЛИКОВАНЫ!")
    elif published_count > 0:
        print(f"⚠️ Опубликовано {published_count} из {len(articles)} статей")
    else:
        print("❌ НИ ОДНА СТАТЬЯ НЕ БЫЛА ОПУБЛИКОВАНА")
    
    return published_count > 0

if __name__ == "__main__":
    success = main()
    exit(0 if success else 1)
