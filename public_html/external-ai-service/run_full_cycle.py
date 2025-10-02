#!/usr/bin/env python3
"""
Полный цикл генерации и публикации статей
"""

import os
import sys
import json
import requests
import logging
from datetime import datetime
from dotenv import load_dotenv

# Загружаем переменные окружения
load_dotenv('config.env')

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('full_cycle.log'),
        logging.StreamHandler()
    ]
)

def run_demo_processor():
    """Запуск демо процессора для генерации статей"""
    print("🤖 Запуск демо процессора...")
    
    try:
        # Импортируем и запускаем демо процессор
        from new_ai_processor import PsychologyArticleProcessor
        
        processor = PsychologyArticleProcessor()
        
        # Запускаем обработку
        print("Генерация статей...")
        articles = processor.process_daily_articles()
        
        if articles:
            print(f"✅ Сгенерировано {len(articles)} статей")
            return articles
        else:
            print("⚠️ Статьи не были сгенерированы")
            return None
            
    except Exception as e:
        print(f"❌ Ошибка генерации статей: {e}")
        return None

def publish_articles(articles):
    """Публикация статей на сайт"""
    print(f"\n📝 Публикация {len(articles)} статей на сайт...")
    
    published_count = 0
    
    for i, article in enumerate(articles, 1):
        print(f"\nПубликация статьи {i}/{len(articles)}: {article.get('title', 'Без названия')}")
        
        try:
            # Подготавливаем данные для публикации
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
                    import re
                    id_match = re.search(r'<strong>ID статьи:</strong> (\d+)', response_text)
                    if id_match:
                        article_id = id_match.group(1)
                        print(f"✅ Статья опубликована (ID: {article_id})")
                        published_count += 1
                    else:
                        print("✅ Статья опубликована")
                        published_count += 1
                else:
                    print("❌ Ошибка публикации статьи")
            else:
                print(f"❌ HTTP ошибка: {response.status_code}")
                
        except Exception as e:
            print(f"❌ Ошибка при публикации статьи: {e}")
    
    return published_count

def save_articles_to_file(articles):
    """Сохранение статей в файл"""
    print("\n💾 Сохранение статей в файл...")
    
    try:
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f"generated_articles_{timestamp}.json"
        
        # Подготавливаем данные для сохранения
        output_data = {
            "processing_date": datetime.now().isoformat(),
            "total_articles": len(articles),
            "articles": articles
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(output_data, f, ensure_ascii=False, indent=2)
        
        print(f"✅ Статьи сохранены в файл: {filename}")
        return filename
        
    except Exception as e:
        print(f"❌ Ошибка сохранения файла: {e}")
        return None

def main():
    """Основная функция полного цикла"""
    print("🚀 ЗАПУСК ПОЛНОГО ЦИКЛА ГЕНЕРАЦИИ И ПУБЛИКАЦИИ")
    print("=" * 60)
    
    # Шаг 1: Генерация статей
    print("\n" + "="*20 + " ГЕНЕРАЦИЯ СТАТЕЙ " + "="*20)
    articles = run_demo_processor()
    
    if not articles:
        print("❌ Не удалось сгенерировать статьи. Завершение работы.")
        return False
    
    # Шаг 2: Сохранение в файл
    print("\n" + "="*20 + " СОХРАНЕНИЕ " + "="*20)
    filename = save_articles_to_file(articles)
    
    # Шаг 3: Публикация на сайт
    print("\n" + "="*20 + " ПУБЛИКАЦИЯ " + "="*20)
    published_count = publish_articles(articles)
    
    # Итоговый отчет
    print("\n" + "="*60)
    print("📊 ИТОГОВЫЙ ОТЧЕТ ПОЛНОГО ЦИКЛА")
    print("="*60)
    
    print(f"📝 Сгенерировано статей: {len(articles)}")
    print(f"💾 Сохранено в файл: {'Да' if filename else 'Нет'}")
    print(f"🌐 Опубликовано на сайте: {published_count}")
    
    if published_count == len(articles):
        print("🎉 ВСЕ СТАТЬИ УСПЕШНО ОПУБЛИКОВАНЫ!")
        success = True
    elif published_count > 0:
        print(f"⚠️ Опубликовано {published_count} из {len(articles)} статей")
        success = True
    else:
        print("❌ НИ ОДНА СТАТЬЯ НЕ БЫЛА ОПУБЛИКОВАНА")
        success = False
    
    print(f"\n📁 Файл с результатами: {filename}")
    print(f"📋 Лог файл: full_cycle.log")
    
    return success

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
