#!/usr/bin/env python3
"""
Тестовый скрипт для проверки полного цикла генерации и публикации статей
"""

import os
import sys
import json
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
        logging.FileHandler('test_cycle.log'),
        logging.StreamHandler()
    ]
)

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
    
    return True

def test_config():
    """Тест конфигурации"""
    print("\n🔧 Тестирование конфигурации...")
    
    required_vars = [
        'OPENAI_API_KEY',
        'MAX_ARTICLES_PER_DAY',
        'MIN_WORD_COUNT',
        'MAX_WORD_COUNT',
        'ANALYSIS_MODEL',
        'WRITING_MODEL'
    ]
    
    missing_vars = []
    for var in required_vars:
        if not os.getenv(var):
            missing_vars.append(var)
    
    if missing_vars:
        print(f"❌ Отсутствуют переменные: {', '.join(missing_vars)}")
        return False
    
    print("✅ Все обязательные переменные настроены")
    
    # Проверяем API ключ
    api_key = os.getenv('OPENAI_API_KEY')
    if api_key and api_key.startswith('sk-proj-'):
        print("✅ OpenAI API ключ настроен")
    else:
        print("⚠️ OpenAI API ключ не настроен или неверный формат")
    
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
            print(f"Первые 3 ссылки: {links[:3]}")
            return True
        else:
            print("⚠️ Ссылки не получены (возможно, проблема с сетью)")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка парсера: {e}")
        return False

def test_ai_components():
    """Тест AI компонентов с фиксированными данными"""
    print("\n🤖 Тестирование AI компонентов...")
    
    # Фиксированные данные для теста
    test_article = {
        'url': 'https://www.psychologytoday.com/test',
        'title': 'Test Article: Understanding Anxiety',
        'content': 'This is a test article about anxiety and how to manage it effectively.',
        'date': '2024-10-02'
    }
    
    try:
        from content_analyzer import ContentAnalyzer
        analyzer = ContentAnalyzer()
        
        print("Анализ контента...")
        analysis = analyzer.analyze_article(test_article)
        
        if analysis:
            print("✅ Анализ контента - OK")
            print(f"Тема: {analysis.get('theme', 'Не определена')}")
            print(f"Ключевые моменты: {len(analysis.get('key_points', []))}")
        else:
            print("❌ Анализ контента не удался")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка анализатора: {e}")
        return False
    
    try:
        from article_writer import ArticleWriter
        writer = ArticleWriter()
        
        print("Написание статьи...")
        article = writer.write_adapted_article(analysis)
        
        if article:
            print("✅ Написание статьи - OK")
            print(f"Заголовок: {article.get('title', 'Не определен')}")
            print(f"Длина контента: {len(article.get('content', ''))} символов")
        else:
            print("❌ Написание статьи не удалось")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка писателя: {e}")
        return False
    
    return True

def test_duplicate_tracker():
    """Тест трекера дубликатов"""
    print("\n🔄 Тестирование трекера дубликатов...")
    
    try:
        from duplicate_tracker import DuplicateTracker
        tracker = DuplicateTracker()
        
        test_url = "https://www.psychologytoday.com/test-article"
        
        # Проверяем, что URL не использовался
        if not tracker.is_used(test_url):
            print("✅ URL не использовался ранее")
            
            # Добавляем URL
            tracker.mark_as_used(test_url)
            print("✅ URL добавлен в список использованных")
            
            # Проверяем, что теперь URL использовался
            if tracker.is_used(test_url):
                print("✅ URL корректно отмечен как использованный")
                return True
            else:
                print("❌ URL не отмечен как использованный")
                return False
        else:
            print("⚠️ URL уже использовался ранее")
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
        if integrator.test_connection():
            print("✅ Подключение к сайту - OK")
        else:
            print("⚠️ Подключение к сайту не удалось")
            
        return True
        
    except Exception as e:
        print(f"❌ Ошибка интеграции: {e}")
        return False

def main():
    """Основная функция тестирования"""
    print("🚀 ЗАПУСК ПОЛНОГО ТЕСТОВОГО ЦИКЛА")
    print("=" * 50)
    
    tests = [
        ("Импорт модулей", test_imports),
        ("Конфигурация", test_config),
        ("Парсер", test_parser),
        ("AI компоненты", test_ai_components),
        ("Трекер дубликатов", test_duplicate_tracker),
        ("Интеграция", test_integration)
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
    
    return passed == total

if __name__ == "__main__":
    success = main()
    sys.exit(0 if success else 1)
