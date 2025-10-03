#!/usr/bin/env python3
"""
Тест публикации статей на сайт
"""

import json
import os
import requests
from datetime import datetime

def test_site_connection():
    """Тест подключения к сайту"""
    print("🌐 Тестирование подключения к сайту...")
    
    try:
        # Тестируем основную страницу
        response = requests.get("https://cherkas-therapy.ru", timeout=10)
        if response.status_code == 200:
            print("✅ Основная страница доступна")
        else:
            print(f"⚠️ Основная страница недоступна (код: {response.status_code})")
        
        # Тестируем API для загрузки статей
        response = requests.get("https://cherkas-therapy.ru/upload_article_smart.php", timeout=10)
        if response.status_code == 200:
            print("✅ API для загрузки статей доступен")
        else:
            print(f"⚠️ API для загрузки статей недоступен (код: {response.status_code})")
        
        return True
        
    except Exception as e:
        print(f"❌ Ошибка подключения к сайту: {e}")
        return False

def test_article_upload():
    """Тест загрузки статьи"""
    print("\n📝 Тестирование загрузки статьи...")
    
    # Создаем тестовую статью
    test_article = {
        "title": "Тестовая статья для проверки системы",
        "content": """
        <h1>Тестовая статья для проверки системы</h1>
        <p>Это тестовая статья, созданная для проверки работоспособности системы публикации.</p>
        <h2>Основные разделы:</h2>
        <ul>
        <li>Введение</li>
        <li>Основная часть</li>
        <li>Заключение</li>
        </ul>
        <p>Статья создана: """ + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + """</p>
        """,
        "excerpt": "Тестовая статья для проверки системы публикации статей",
        "meta_title": "Тестовая статья - Система публикации",
        "meta_description": "Проверка работоспособности системы автоматической публикации статей",
        "tags": ["тест", "система", "публикация"],
        "category_id": 1,
        "author": "Денис Черкас",
        "is_active": 1
    }
    
    try:
        # Отправляем статью на сайт
        response = requests.post(
            "https://cherkas-therapy.ru/upload_article_smart.php",
            json=test_article,
            headers={'Content-Type': 'application/json'},
            timeout=30
        )
        
        if response.status_code == 200:
            # API возвращает HTML, а не JSON
            response_text = response.text
            
            if "Статья успешно добавлена!" in response_text:
                print("✅ Статья успешно загружена")
                
                # Извлекаем ID статьи из HTML
                import re
                id_match = re.search(r'<strong>ID статьи:</strong> (\d+)', response_text)
                if id_match:
                    article_id = id_match.group(1)
                    print(f"  ID статьи: {article_id}")
                
                # Извлекаем название
                title_match = re.search(r'<strong>Название:</strong> ([^<]+)', response_text)
                if title_match:
                    title = title_match.group(1).strip()
                    print(f"  Название: {title}")
                
                return True
            else:
                print("❌ Статья не была добавлена")
                print(f"Ответ сервера: {response_text[:500]}...")
                return False
        else:
            print(f"❌ HTTP ошибка: {response.status_code}")
            print(f"Ответ: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка при загрузке статьи: {e}")
        return False

def test_demo_article_publication():
    """Тест публикации демо статьи"""
    print("\n🎭 Тестирование публикации демо статьи...")
    
    # Ищем демо файл
    demo_files = [f for f in os.listdir('.') if f.startswith('psychology_articles_demo_') and f.endswith('.json')]
    
    if not demo_files:
        print("⚠️ Демо файлы не найдены")
        return False
    
    demo_file = demo_files[0]
    print(f"Используем демо файл: {demo_file}")
    
    try:
        with open(demo_file, 'r', encoding='utf-8') as f:
            demo_data = json.load(f)
        
        if isinstance(demo_data, dict) and 'articles' in demo_data:
            articles = demo_data['articles']
        elif isinstance(demo_data, list):
            articles = demo_data
        else:
            print("❌ Неверный формат демо файла")
            return False
        
        if not articles:
            print("❌ Демо файл не содержит статей")
            return False
        
        # Берем первую статью
        article = articles[0]
        print(f"Публикуем статью: {article.get('title', 'Без названия')}")
        
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
            # API возвращает HTML, а не JSON
            response_text = response.text
            
            if "Статья успешно добавлена!" in response_text:
                print("✅ Демо статья успешно опубликована")
                
                # Извлекаем ID статьи из HTML
                import re
                id_match = re.search(r'<strong>ID статьи:</strong> (\d+)', response_text)
                if id_match:
                    article_id = id_match.group(1)
                    print(f"  ID статьи: {article_id}")
                
                # Извлекаем название
                title_match = re.search(r'<strong>Название:</strong> ([^<]+)', response_text)
                if title_match:
                    title = title_match.group(1).strip()
                    print(f"  Название: {title}")
                
                return True
            else:
                print("❌ Демо статья не была опубликована")
                print(f"Ответ сервера: {response_text[:500]}...")
                return False
        else:
            print(f"❌ HTTP ошибка: {response.status_code}")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка при публикации демо статьи: {e}")
        return False

def main():
    """Основная функция тестирования публикации"""
    print("🚀 ТЕСТ ПУБЛИКАЦИИ СТАТЕЙ НА САЙТ")
    print("=" * 50)
    
    tests = [
        ("Подключение к сайту", test_site_connection),
        ("Загрузка тестовой статьи", test_article_upload),
        ("Публикация демо статьи", test_demo_article_publication)
    ]
    
    results = []
    
    for test_name, test_func in tests:
        print(f"\n{'='*20} {test_name} {'='*20}")
        try:
            result = test_func()
            results.append((test_name, result))
        except Exception as e:
            print(f"❌ Критическая ошибка в тесте '{test_name}': {e}")
            results.append((test_name, False))
    
    # Итоговый отчет
    print("\n" + "="*50)
    print("📊 ИТОГОВЫЙ ОТЧЕТ ПУБЛИКАЦИИ")
    print("="*50)
    
    passed = 0
    total = len(results)
    
    for test_name, result in results:
        status = "✅ ПРОЙДЕН" if result else "❌ ПРОВАЛЕН"
        print(f"{test_name}: {status}")
        if result:
            passed += 1
    
    print(f"\nРезультат: {passed}/{total} тестов пройдено")
    
    if passed == total:
        print("🎉 ВСЕ ТЕСТЫ ПУБЛИКАЦИИ ПРОЙДЕНЫ!")
    elif passed >= total * 0.7:
        print("⚠️ Большинство тестов пройдено.")
    else:
        print("❌ Много тестов провалено.")
    
    return passed == total

if __name__ == "__main__":
    import sys
    success = main()
    sys.exit(0 if success else 1)
