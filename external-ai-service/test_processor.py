#!/usr/bin/env python3
"""
Скрипт для тестирования нового AI процессора
"""

import os
import json
import logging
from datetime import datetime

# Импортируем компоненты для тестирования
from parser import PsychologyTodayParser
from content_analyzer import ContentAnalyzer
from article_writer import ArticleWriter
from image_prompter import ImagePrompter
from duplicate_tracker import DuplicateTracker

def test_parser():
    """Тестирование парсера"""
    print("🔍 Тестирование парсера...")
    
    parser = PsychologyTodayParser()
    
    try:
        # Тестируем получение ссылок
        links = parser.get_article_links(5)
        print(f"✅ Получено {len(links)} ссылок")
        
        if links:
            # Тестируем парсинг одной статьи
            article = parser.parse_article(links[0])
            if article:
                print(f"✅ Статья распарсена: {article['title'][:50]}...")
                print(f"   Слов: {article['word_count']}")
                print(f"   Теги: {', '.join(article['tags'][:3])}")
                return article
            else:
                print("❌ Не удалось распарсить статью")
        else:
            print("❌ Не получены ссылки")
            
    except Exception as e:
        print(f"❌ Ошибка парсера: {e}")
    
    return None

def test_analyzer(article):
    """Тестирование анализатора"""
    print("\n🧠 Тестирование анализатора...")
    
    if not article:
        print("❌ Нет статьи для анализа")
        return None
    
    analyzer = ContentAnalyzer()
    
    try:
        analysis = analyzer.analyze_article(article)
        if analysis:
            print(f"✅ Анализ выполнен: {analysis['main_theme']}")
            print(f"   Тон: {analysis['emotional_tone']}")
            print(f"   Фактов: {len(analysis['interesting_facts'])}")
            return analysis
        else:
            print("❌ Не удалось проанализировать статью")
            
    except Exception as e:
        print(f"❌ Ошибка анализатора: {e}")
    
    return None

def test_writer(analysis):
    """Тестирование писателя"""
    print("\n✍️ Тестирование писателя...")
    
    if not analysis:
        print("❌ Нет анализа для написания")
        return None
    
    writer = ArticleWriter()
    
    try:
        article = writer.write_adapted_article(analysis)
        if article:
            print(f"✅ Статья написана: {article['title'][:50]}...")
            print(f"   Категория: {article['category']}")
            print(f"   Слов: {article['word_count']}")
            print(f"   Теги: {', '.join(article['tags'])}")
            return article
        else:
            print("❌ Не удалось написать статью")
            
    except Exception as e:
        print(f"❌ Ошибка писателя: {e}")
    
    return None

def test_image_prompter(article):
    """Тестирование промптера изображений"""
    print("\n🖼️ Тестирование промптера изображений...")
    
    if not article:
        print("❌ Нет статьи для подбора изображения")
        return None
    
    prompter = ImagePrompter()
    
    try:
        image_url = prompter.get_image_for_article(article)
        if image_url:
            print(f"✅ Изображение найдено: {image_url}")
            return image_url
        else:
            print("⚠️ Изображение не найдено (возможно, нет API ключа)")
            
    except Exception as e:
        print(f"❌ Ошибка промптера изображений: {e}")
    
    return None

def test_duplicate_tracker():
    """Тестирование трекера дубликатов"""
    print("\n📝 Тестирование трекера дубликатов...")
    
    tracker = DuplicateTracker("test_used_links.json")
    
    try:
        # Тестовые ссылки
        test_links = [
            "https://example.com/test1",
            "https://example.com/test2"
        ]
        
        # Проверяем новые ссылки
        new_links = tracker.filter_new_links(test_links)
        print(f"✅ Новые ссылки: {len(new_links)}")
        
        # Помечаем как использованные
        tracker.mark_multiple_as_used(test_links)
        print("✅ Ссылки помечены как использованные")
        
        # Проверяем снова
        new_links = tracker.filter_new_links(test_links)
        print(f"✅ Новые ссылки после пометки: {len(new_links)}")
        
        # Статистика
        stats = tracker.get_stats()
        print(f"✅ Статистика: {stats['total_used_links']} использованных ссылок")
        
        # Очистка
        if os.path.exists("test_used_links.json"):
            os.remove("test_used_links.json")
        
    except Exception as e:
        print(f"❌ Ошибка трекера дубликатов: {e}")

def test_full_pipeline():
    """Тестирование полного пайплайна"""
    print("\n🚀 Тестирование полного пайплайна...")
    
    # Проверяем переменные окружения
    if not os.getenv('OPENAI_API_KEY'):
        print("❌ Не установлен OPENAI_API_KEY")
        return
    
    # Тестируем каждый компонент
    article = test_parser()
    if not article:
        return
    
    analysis = test_analyzer(article)
    if not analysis:
        return
    
    adapted_article = test_writer(analysis)
    if not adapted_article:
        return
    
    image_url = test_image_prompter(adapted_article)
    
    # Сохраняем результат
    result = {
        'original_article': article,
        'analysis': analysis,
        'adapted_article': adapted_article,
        'image_url': image_url,
        'test_timestamp': datetime.now().isoformat()
    }
    
    filename = f"test_result_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(result, f, ensure_ascii=False, indent=2)
    
    print(f"\n💾 Результат тестирования сохранен в: {filename}")
    print("🎉 Полный пайплайн работает!")

def main():
    """Основная функция тестирования"""
    print("🧪 ТЕСТИРОВАНИЕ AI ПРОЦЕССОРА ПСИХОЛОГИЧЕСКИХ СТАТЕЙ")
    print("=" * 60)
    
    # Настройка логирования
    logging.basicConfig(level=logging.WARNING)  # Уменьшаем вывод логов
    
    try:
        # Тестируем отдельные компоненты
        test_duplicate_tracker()
        
        # Тестируем полный пайплайн
        test_full_pipeline()
        
    except KeyboardInterrupt:
        print("\n⏹️ Тестирование прервано пользователем")
    except Exception as e:
        print(f"\n❌ Критическая ошибка: {e}")
    
    print("\n🏁 Тестирование завершено")

if __name__ == "__main__":
    main()
