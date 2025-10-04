#!/usr/bin/env python3
"""
Тест исправленного форматирования статей
"""

import os
import sys
from article_writer import ArticleWriter

def test_fixed_formatting():
    """Тестируем исправленное форматирование статей"""
    
    # Проверяем наличие API ключа
    if not os.getenv('OPENAI_API_KEY'):
        print("❌ OPENAI_API_KEY не установлен")
        return False
    
    # Тестовые данные
    test_analysis = {
        'main_theme': 'Стресс и тревога в современном мире',
        'main_message': 'Стресс можно контролировать с помощью правильных техник',
        'interesting_facts': [
            '90% людей испытывают стресс ежедневно',
            'Стресс влияет на иммунную систему',
            'Медитация снижает уровень кортизола на 25%'
        ],
        'hidden_truths': [
            'Психологи не всегда говорят о побочных эффектах антидепрессантов',
            'Многие техники релаксации работают только при регулярном применении',
            'Стресс может быть полезным в малых дозах'
        ],
        'practical_advice': [
            'Техника 4-7-8 для быстрого успокоения',
            'Прогрессивная мышечная релаксация',
            'Дыхательные упражнения'
        ]
    }
    
    print("🧪 ТЕСТ ИСПРАВЛЕННОГО ФОРМАТИРОВАНИЯ СТАТЕЙ")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест улучшенной генерации с исправленным форматированием
        print("\n📝 Генерация статьи с исправленным форматированием...")
        article = writer.write_adapted_article_enhanced(test_analysis)
        
        if article:
            print("\n✅ СТАТЬЯ УСПЕШНО СГЕНЕРИРОВАНА И ОТФОРМАТИРОВАНА!")
            print("=" * 60)
            
            # Основная информация
            print(f"📰 Заголовок: {article['title']}")
            print(f"📏 Длина: {len(article['content'])} символов")
            print(f"📝 Слов: {article['word_count']}")
            print(f"🏷️ Категория: {article['category']}")
            print(f"🔖 Теги: {', '.join(article['tags'])}")
            
            # Проверяем новые поля
            print(f"\n📋 ПОЛЯ СТАТЬИ:")
            print(f"📄 Краткое описание: {article.get('short_description', 'НЕТ')[:100]}...")
            print(f"📄 Excerpt: {article.get('excerpt', 'НЕТ')[:100]}...")
            
            # Анализ исправленного форматирования
            print("\n🔍 АНАЛИЗ ИСПРАВЛЕННОГО ФОРМАТИРОВАНИЯ:")
            print("-" * 50)
            
            content = article['content']
            
            # Проверяем отсутствие лишних элементов
            bad_elements = ['**Описание:**', '**Статья:**', '**Краткое описание:**', 'Размер текста:', 'A-', 'A+']
            found_bad = [elem for elem in bad_elements if elem in content]
            print(f"❌ Лишние элементы: {len(found_bad)}/{len(bad_elements)} - {found_bad}")
            
            # Проверяем HTML-теги
            html_tags = ['<h2>Введение</h2>', '<h2>Анализ причин</h2>', '<h2>Практические техники</h2>', '<h2>Профилактика и выводы</h2>', '<h2>Часто задаваемые вопросы</h2>']
            found_tags = [tag for tag in html_tags if tag in content]
            print(f"✅ HTML-заголовки: {len(found_tags)}/{len(html_tags)} - {found_tags}")
            
            # Проверяем описание в начале
            has_description = '<div class="article-description">' in content
            print(f"✅ Описание в начале: {'ДА' if has_description else 'НЕТ'}")
            
            # Проверяем FAQ
            has_faq = '<h2>Часто задаваемые вопросы</h2>' in content
            print(f"✅ FAQ раздел: {'ДА' if has_faq else 'НЕТ'}")
            
            # Проверяем структуру
            structure_ok = len(found_tags) >= 4 and has_description and has_faq
            print(f"✅ Общая структура: {'ОТЛИЧНО' if structure_ok else 'ТРЕБУЕТ УЛУЧШЕНИЯ'}")
            
            # Показываем фрагменты
            print("\n📄 ФРАГМЕНТЫ СТАТЬИ:")
            print("-" * 50)
            print("НАЧАЛО СТАТЬИ (первые 600 символов):")
            print(content[:600] + "...")
            
            print("\nСЕРЕДИНА СТАТЬИ (фрагмент с заголовками):")
            # Находим первый заголовок
            h2_start = content.find('<h2>')
            if h2_start != -1:
                middle_start = max(0, h2_start - 100)
                middle_end = min(len(content), h2_start + 400)
                print("..." + content[middle_start:middle_end] + "...")
            
            print("\nКОНЕЦ СТАТЬИ (последние 400 символов):")
            print("..." + content[-400:])
            
            # Общая оценка исправленного форматирования
            formatting_score = 0
            if len(found_bad) == 0:
                formatting_score += 3  # Отсутствие лишних элементов
            if len(found_tags) >= 4:
                formatting_score += 3  # Правильные HTML-заголовки
            if has_description:
                formatting_score += 2  # Описание в начале
            if has_faq:
                formatting_score += 2  # FAQ раздел
            
            print(f"\n🎯 ОЦЕНКА ИСПРАВЛЕННОГО ФОРМАТИРОВАНИЯ: {formatting_score}/10")
            
            if formatting_score >= 9:
                print("🌟 ОТЛИЧНО! Форматирование исправлено идеально")
            elif formatting_score >= 7:
                print("👍 ХОРОШО! Форматирование значительно улучшено")
            elif formatting_score >= 5:
                print("⚠️ УДОВЛЕТВОРИТЕЛЬНО! Есть улучшения, но нужны доработки")
            else:
                print("❌ ПЛОХО! Форматирование требует серьезных исправлений")
            
            # Сравнение с предыдущими проблемами
            print(f"\n📊 СРАВНЕНИЕ С ПРЕДЫДУЩИМИ ПРОБЛЕМАМИ:")
            print("-" * 50)
            print(f"❌ Было: Дублирование **Описание:** - {'ИСПРАВЛЕНО' if '**Описание:**' not in content else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Лишние элементы (A-, A+, Размер текста) - {'ИСПРАВЛЕНО' if len(found_bad) == 0 else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Неправильные заголовки - {'ИСПРАВЛЕНО' if len(found_tags) >= 4 else 'НЕ ИСПРАВЛЕНО'}")
            print(f"✅ Стало: Чистая структура с HTML-тегами - {'ДА' if structure_ok else 'НЕТ'}")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_fixed_formatting()
    sys.exit(0 if success else 1)
