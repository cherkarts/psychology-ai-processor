#!/usr/bin/env python3
"""
Тест исправлений заголовков и форматирования
"""

import os
import sys
from article_writer import ArticleWriter

def test_title_and_formatting_fixes():
    """Тестируем исправления заголовков и форматирования"""
    
    # Проверяем наличие API ключа
    if not os.getenv('OPENAI_API_KEY'):
        print("❌ OPENAI_API_KEY не установлен")
        return False
    
    # Тестовые данные
    test_analysis = {
        'main_theme': 'Бей или беги: реакция на стресс',
        'main_message': 'Понимание реакции "бей или беги" помогает управлять стрессом',
        'interesting_facts': [
            'Реакция "бей или беги" - древний механизм выживания',
            'Стресс активирует симпатическую нервную систему',
            'Хронический стресс может привести к истощению'
        ],
        'hidden_truths': [
            'Не все стрессовые реакции одинаковы',
            'Реакция "бей или беги" может быть адаптивной',
            'Современный стресс часто не требует физической реакции'
        ],
        'practical_advice': [
            'Техника 4-7-8 для быстрого успокоения',
            'Прогрессивная мышечная релаксация',
            'Дыхательные упражнения'
        ]
    }
    
    print("🧪 ТЕСТ ИСПРАВЛЕНИЙ ЗАГОЛОВКОВ И ФОРМАТИРОВАНИЯ")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест улучшенной генерации с исправлениями
        print("\n📝 Генерация статьи с исправлениями...")
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
            
            # Проверяем краткое описание
            short_desc = article.get('short_description', '')
            print(f"📄 Краткое описание: {short_desc[:100]}...")
            
            # Анализ исправлений
            print("\n🔍 АНАЛИЗ ИСПРАВЛЕНИЙ:")
            print("-" * 50)
            
            content = article['content']
            
            # 1. Проверяем исправление заголовка "бой или беги" → "Бей или беги"
            title_correct = 'бой или беги' not in article['title'].lower()
            title_has_correct = 'Бей или беги' in article['title']
            print(f"✅ Заголовок исправлен: {'ДА' if title_correct and title_has_correct else 'НЕТ'} - {article['title']}")
            
            # 2. Проверяем отсутствие "Введение" в кратком описании
            no_introduction_in_desc = 'введение' not in short_desc.lower()
            print(f"✅ Нет 'Введение' в описании: {'ДА' if no_introduction_in_desc else 'НЕТ'}")
            
            # 3. Проверяем форматирование практических техник
            tech_start = content.find('<h2>Практические техники</h2>')
            if tech_start != -1:
                tech_section = content[tech_start:tech_start + 1000]
                has_ul_li = '<ul>' in tech_section and '<li>' in tech_section
                has_strong = '<strong>' in tech_section
                no_asterisks = '**' not in tech_section
                tech_properly_formatted = has_ul_li and has_strong and no_asterisks
            else:
                tech_properly_formatted = False
            print(f"✅ Практические техники отформатированы: {'ДА' if tech_properly_formatted else 'НЕТ'}")
            
            # 4. Проверяем наличие заголовка H1
            has_h1 = '<h1>' in content and '</h1>' in content
            print(f"✅ Заголовок H1: {'ЕСТЬ' if has_h1 else 'НЕТ'}")
            
            # 5. Проверяем отсутствие лишних элементов
            bad_elements = ['**Описание:**', '**Статья:**', '**Краткое описание:**', 'Размер текста:', 'A-', 'A+']
            found_bad = [elem for elem in bad_elements if elem in content]
            print(f"❌ Лишние элементы: {len(found_bad)}/{len(bad_elements)} - {found_bad}")
            
            # 6. Проверяем HTML-теги заголовков
            html_tags = ['<h2>Введение</h2>', '<h2>Анализ причин</h2>', '<h2>Практические техники</h2>', '<h2>Профилактика и выводы</h2>', '<h2>Часто задаваемые вопросы</h2>']
            found_tags = [tag for tag in html_tags if tag in content]
            print(f"✅ HTML-заголовки: {len(found_tags)}/{len(html_tags)} - {found_tags}")
            
            # Показываем фрагменты
            print("\n📄 ФРАГМЕНТЫ СТАТЬИ:")
            print("-" * 50)
            print("НАЧАЛО СТАТЬИ (первые 500 символов):")
            print(content[:500] + "...")
            
            # Показываем практические техники
            if tech_start != -1:
                print("\nПРАКТИЧЕСКИЕ ТЕХНИКИ:")
                tech_end = content.find('<h2>', tech_start + 1)
                if tech_end == -1:
                    tech_end = tech_start + 1200
                print(content[tech_start:tech_end] + "...")
            
            # Показываем FAQ раздел
            faq_start = content.find('<h2>Часто задаваемые вопросы</h2>')
            if faq_start != -1:
                print("\nFAQ РАЗДЕЛ:")
                faq_end = content.find('</ul>', faq_start) + 5
                if faq_end > faq_start:
                    print(content[faq_start:faq_end])
                else:
                    print(content[faq_start:faq_start + 800] + "...")
            
            # Общая оценка исправлений
            fixes_score = 0
            if title_correct and title_has_correct:
                fixes_score += 2  # Заголовок исправлен
            if no_introduction_in_desc:
                fixes_score += 2  # Нет "Введение" в описании
            if tech_properly_formatted:
                fixes_score += 2  # Практические техники отформатированы
            if has_h1:
                fixes_score += 1  # Есть заголовок H1
            if len(found_bad) == 0:
                fixes_score += 1  # Нет лишних элементов
            if len(found_tags) >= 4:
                fixes_score += 2  # Правильные HTML-заголовки
            
            print(f"\n🎯 ОЦЕНКА ИСПРАВЛЕНИЙ: {fixes_score}/10")
            
            if fixes_score >= 9:
                print("🌟 ОТЛИЧНО! Все исправления работают идеально")
            elif fixes_score >= 7:
                print("👍 ХОРОШО! Большинство исправлений работают")
            elif fixes_score >= 5:
                print("⚠️ УДОВЛЕТВОРИТЕЛЬНО! Есть улучшения, но нужны доработки")
            else:
                print("❌ ПЛОХО! Исправления требуют серьезной доработки")
            
            # Сравнение с предыдущими проблемами
            print(f"\n📊 СРАВНЕНИЕ С ПРЕДЫДУЩИМИ ПРОБЛЕМАМИ:")
            print("-" * 50)
            print(f"❌ Было: 'бой или беги' - {'ИСПРАВЛЕНО' if title_correct and title_has_correct else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: 'Введение' в кратком описании - {'ИСПРАВЛЕНО' if no_introduction_in_desc else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Неправильное форматирование техник - {'ИСПРАВЛЕНО' if tech_properly_formatted else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Списки в одну строку - {'ИСПРАВЛЕНО' if tech_properly_formatted else 'НЕ ИСПРАВЛЕНО'}")
            print(f"✅ Стало: Чистая структура с правильным форматированием - {'ДА' if fixes_score >= 8 else 'НЕТ'}")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_title_and_formatting_fixes()
    sys.exit(0 if success else 1)
