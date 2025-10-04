#!/usr/bin/env python3
"""
Тест исправлений подзаголовков и заголовков статей
"""

import os
import sys
from article_writer import ArticleWriter

def test_subheading_fixes():
    """Тестируем исправления подзаголовков и заголовков"""
    
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
    
    print("🧪 ТЕСТ ИСПРАВЛЕНИЙ ПОДЗАГОЛОВКОВ И ЗАГОЛОВКОВ")
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
            
            # Анализ исправлений
            print("\n🔍 АНАЛИЗ ИСПРАВЛЕНИЙ:")
            print("-" * 50)
            
            content = article['content']
            
            # 1. Проверяем наличие заголовка H1
            has_h1 = '<h1>' in content and '</h1>' in content
            print(f"✅ Заголовок H1: {'ЕСТЬ' if has_h1 else 'НЕТ'}")
            
            # 2. Проверяем подзаголовки без звездочек
            has_strong_without_asterisks = '<strong>' in content and '**' not in content
            print(f"✅ Подзаголовки без звездочек: {'ДА' if has_strong_without_asterisks else 'НЕТ'}")
            
            # 3. Проверяем правильное форматирование списков
            has_ul_li = '<ul>' in content and '<li>' in content
            print(f"✅ Маркерованные списки: {'ДА' if has_ul_li else 'НЕТ'}")
            
            # 4. Проверяем разделение на абзацы
            has_paragraphs = content.count('<p>') > 0 or content.count('\n\n') > 0
            print(f"✅ Разделение на абзацы: {'ДА' if has_paragraphs else 'НЕТ'}")
            
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
            print("НАЧАЛО СТАТЬИ (первые 600 символов):")
            print(content[:600] + "...")
            
            # Показываем практические техники
            tech_start = content.find('<h2>Практические техники</h2>')
            if tech_start != -1:
                print("\nПРАКТИЧЕСКИЕ ТЕХНИКИ:")
                tech_end = content.find('<h2>', tech_start + 1)
                if tech_end == -1:
                    tech_end = tech_start + 1000
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
            if has_h1:
                fixes_score += 2  # Есть заголовок H1
            if has_strong_without_asterisks:
                fixes_score += 2  # Подзаголовки без звездочек
            if has_ul_li:
                fixes_score += 2  # Маркерованные списки
            if has_paragraphs:
                fixes_score += 2  # Разделение на абзацы
            if len(found_bad) == 0:
                fixes_score += 1  # Нет лишних элементов
            if len(found_tags) >= 4:
                fixes_score += 1  # Правильные HTML-заголовки
            
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
            print(f"❌ Было: Нет заголовка статьи - {'ИСПРАВЛЕНО' if has_h1 else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Подзаголовки со звездочками - {'ИСПРАВЛЕНО' if has_strong_without_asterisks else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Списки не разделены на абзацы - {'ИСПРАВЛЕНО' if has_paragraphs else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Неправильное форматирование - {'ИСПРАВЛЕНО' if has_ul_li else 'НЕ ИСПРАВЛЕНО'}")
            print(f"✅ Стало: Чистая структура с правильным форматированием - {'ДА' if fixes_score >= 8 else 'НЕТ'}")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_subheading_fixes()
    sys.exit(0 if success else 1)
