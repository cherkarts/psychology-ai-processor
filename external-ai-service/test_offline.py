#!/usr/bin/env python3
"""
Офлайн тест для проверки компонентов без OpenAI API
"""

import os
import sys
import json
import logging
from datetime import datetime

def test_imports():
    """Тест импорта всех модулей"""
    print("🔍 Тестирование импорта модулей...")
    
    try:
        from parser import PsychologyTodayParser
        print("✅ parser.py - OK")
    except Exception as e:
        print(f"❌ parser.py - ОШИБКА: {e}")
        return False
    
    try:
        from content_analyzer import ContentAnalyzer
        print("✅ content_analyzer.py - OK")
    except Exception as e:
        print(f"❌ content_analyzer.py - ОШИБКА: {e}")
        return False
    
    try:
        from article_writer import ArticleWriter
        print("✅ article_writer.py - OK")
    except Exception as e:
        print(f"❌ article_writer.py - ОШИБКА: {e}")
        return False
    
    try:
        from image_prompter import ImagePrompter
        print("✅ image_prompter.py - OK")
    except Exception as e:
        print(f"❌ image_prompter.py - ОШИБКА: {e}")
        return False
    
    try:
        from duplicate_tracker import DuplicateTracker
        print("✅ duplicate_tracker.py - OK")
    except Exception as e:
        print(f"❌ duplicate_tracker.py - ОШИБКА: {e}")
        return False
    
    try:
        from new_ai_processor import PsychologyArticleProcessor
        print("✅ new_ai_processor.py - OK")
    except Exception as e:
        print(f"❌ new_ai_processor.py - ОШИБКА: {e}")
        return False
    
    try:
        from integration_script import SiteIntegration
        print("✅ integration_script.py - OK")
    except Exception as e:
        print(f"❌ integration_script.py - ОШИБКА: {e}")
        return False
    
    return True

def test_parser():
    """Тест парсера"""
    print("\n📰 Тестирование парсера...")
    
    try:
        from parser import PsychologyTodayParser
        parser = PsychologyTodayParser()
        
        # Тестируем получение ссылок
        print("Получение ссылок на статьи...")
        links = parser.get_article_links()
        
        if links:
            print(f"✅ Получено {len(links)} ссылок")
            print(f"Первые 3 ссылки:")
            for i, link in enumerate(links[:3], 1):
                print(f"  {i}. {link}")
            return True
        else:
            print("⚠️ Ссылки не получены (возможно, проблема с сетью)")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка парсера: {e}")
        return False

def test_duplicate_tracker():
    """Тест трекера дубликатов"""
    print("\n🔄 Тестирование трекера дубликатов...")
    
    try:
        from duplicate_tracker import DuplicateTracker
        tracker = DuplicateTracker()
        
        test_urls = [
            "https://www.psychologytoday.com/test-article-1",
            "https://www.psychologytoday.com/test-article-2",
            "https://www.psychologytoday.com/test-article-1"  # Дубликат
        ]
        
        for url in test_urls:
            if not tracker.is_used(url):
                print(f"✅ URL не использовался: {url}")
                tracker.mark_as_used(url)
                print(f"✅ URL добавлен: {url}")
            else:
                print(f"⚠️ URL уже использовался: {url}")
        
        # Проверяем статистику
        stats = tracker.get_stats()
        print(f"✅ Статистика: {stats}")
        
        return True
            
    except Exception as e:
        print(f"❌ Ошибка трекера дубликатов: {e}")
        return False

def test_integration():
    """Тест интеграции с сайтом"""
    print("\n🌐 Тестирование интеграции с сайтом...")
    
    try:
        from integration_script import SiteIntegration
        integrator = SiteIntegration("https://cherkas-therapy.ru")
        
        # Тестируем подключение к сайту
        print("Тестирование подключения к сайту...")
        if integrator.test_connection():
            print("✅ Подключение к сайту - OK")
        else:
            print("⚠️ Подключение к сайту не удалось")
            
        return True
        
    except Exception as e:
        print(f"❌ Ошибка интеграции: {e}")
        return False

def test_file_structure():
    """Тест структуры файлов"""
    print("\n📁 Тестирование структуры файлов...")
    
    required_files = [
        'config.env',
        'requirements.txt',
        'parser.py',
        'content_analyzer.py',
        'article_writer.py',
        'image_prompter.py',
        'duplicate_tracker.py',
        'new_ai_processor.py',
        'integration_script.py'
    ]
    
    missing_files = []
    for file in required_files:
        if not os.path.exists(file):
            missing_files.append(file)
        else:
            print(f"✅ {file} - найден")
    
    if missing_files:
        print(f"❌ Отсутствуют файлы: {', '.join(missing_files)}")
        return False
    
    return True

def test_config_files():
    """Тест конфигурационных файлов"""
    print("\n🔧 Тестирование конфигурационных файлов...")
    
    # Проверяем config.env
    if os.path.exists('config.env'):
        print("✅ config.env - найден")
        try:
            with open('config.env', 'r', encoding='utf-8') as f:
                content = f.read()
                if 'OPENAI_API_KEY' in content:
                    print("✅ OPENAI_API_KEY - настроен")
                else:
                    print("⚠️ OPENAI_API_KEY - не найден")
        except Exception as e:
            print(f"❌ Ошибка чтения config.env: {e}")
            return False
    else:
        print("❌ config.env - не найден")
        return False
    
    # Проверяем requirements.txt
    if os.path.exists('requirements.txt'):
        print("✅ requirements.txt - найден")
        try:
            with open('requirements.txt', 'r', encoding='utf-8') as f:
                content = f.read()
                required_packages = ['requests', 'beautifulsoup4', 'openai', 'python-dotenv']
                for package in required_packages:
                    if package in content:
                        print(f"✅ {package} - в requirements.txt")
                    else:
                        print(f"⚠️ {package} - не найден в requirements.txt")
        except Exception as e:
            print(f"❌ Ошибка чтения requirements.txt: {e}")
            return False
    else:
        print("❌ requirements.txt - не найден")
        return False
    
    return True

def test_demo_data():
    """Тест с демо данными"""
    print("\n🎭 Тестирование с демо данными...")
    
    # Проверяем, есть ли демо файл
    demo_files = [f for f in os.listdir('.') if f.startswith('psychology_articles_demo_') and f.endswith('.json')]
    
    if demo_files:
        demo_file = demo_files[0]
        print(f"✅ Найден демо файл: {demo_file}")
        
        try:
            with open(demo_file, 'r', encoding='utf-8') as f:
                demo_data = json.load(f)
            
            # Проверяем структуру файла
            if isinstance(demo_data, dict) and 'articles' in demo_data:
                articles = demo_data['articles']
                if isinstance(articles, list) and len(articles) > 0:
                    article = articles[0]
                    print(f"✅ Демо статья загружена")
                    print(f"  Заголовок: {article.get('title', 'Не определен')}")
                    print(f"  Длина контента: {len(article.get('content', ''))} символов")
                    print(f"  Теги: {', '.join(article.get('tags', []))}")
                    print(f"  Всего статей в файле: {len(articles)}")
                    return True
                else:
                    print("❌ Демо файл не содержит статей")
                    return False
            elif isinstance(demo_data, list) and len(demo_data) > 0:
                article = demo_data[0]
                print(f"✅ Демо статья загружена (старый формат)")
                print(f"  Заголовок: {article.get('title', 'Не определен')}")
                print(f"  Длина контента: {len(article.get('content', ''))} символов")
                print(f"  Теги: {', '.join(article.get('tags', []))}")
                return True
            else:
                print("❌ Демо файл пуст или некорректный")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка чтения демо файла: {e}")
            return False
    else:
        print("⚠️ Демо файлы не найдены")
        return True  # Не критично

def main():
    """Основная функция тестирования"""
    print("🚀 ЗАПУСК ОФЛАЙН ТЕСТОВОГО ЦИКЛА")
    print("=" * 50)
    
    tests = [
        ("Структура файлов", test_file_structure),
        ("Конфигурационные файлы", test_config_files),
        ("Импорт модулей", test_imports),
        ("Парсер", test_parser),
        ("Трекер дубликатов", test_duplicate_tracker),
        ("Интеграция", test_integration),
        ("Демо данные", test_demo_data)
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
    print("📊 ИТОГОВЫЙ ОТЧЕТ")
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
        print("🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ! Система готова к работе.")
    elif passed >= total * 0.8:
        print("⚠️ Большинство тестов пройдено. Система частично готова.")
    else:
        print("❌ Много тестов провалено. Требуется настройка.")
    
    print("\n💡 РЕКОМЕНДАЦИИ:")
    print("1. Для полного тестирования AI компонентов нужен VPN")
    print("2. Убедитесь, что OpenAI API ключ настроен в config.env")
    print("3. Проверьте подключение к интернету")
    
    return passed == total

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
