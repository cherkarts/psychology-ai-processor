#!/usr/bin/env python3
"""
Тест финальных исправлений форматирования статей
"""

import os
import sys
from article_writer import ArticleWriter

def test_final_fixes():
    """Тестируем финальные исправления форматирования"""
    
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
    
    print("🧪 ТЕСТ ФИНАЛЬНЫХ ИСПРАВЛЕНИЙ ФОРМАТИРОВАНИЯ")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест улучшенной генерации с финальными исправлениями
        print("\n📝 Генерация статьи с финальными исправлениями...")
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
            
            # Анализ финальных исправлений
            print("\n🔍 АНАЛИЗ ФИНАЛЬНЫХ ИСПРАВЛЕНИЙ:")
            print("-" * 50)
            
            content = article['content']
            
            # 1. Проверяем наличие заголовка
            has_title = bool(article['title'] and article['title'] != "Психологическая статья")
            print(f"✅ Заголовок статьи: {'ЕСТЬ' if has_title else 'НЕТ'} - {article['title']}")
            
            # 2. Проверяем краткое описание (не должно содержать название)
            short_desc = article.get('short_description', '')
            title_in_desc = article['title'].lower() in short_desc.lower() if article['title'] else False
            print(f"✅ Краткое описание без названия: {'ДА' if not title_in_desc else 'НЕТ'}")
            
            # 3. Проверяем форматирование списков
            has_ul_tags = '<ul>' in content and '</ul>' in content
            has_li_tags = '<li>' in content and '</li>' in content
            print(f"✅ Маркерованные списки: {'ДА' if has_ul_tags and has_li_tags else 'НЕТ'}")
            
            # 4. Проверяем форматирование FAQ
            faq_start = content.find('<h2>Часто задаваемые вопросы</h2>')
            if faq_start != -1:
                faq_section = content[faq_start:faq_start + 1000]  # Берем первые 1000 символов FAQ
                faq_has_ul = '<ul>' in faq_section
                faq_has_li = '<li>' in faq_section
                faq_properly_formatted = faq_has_ul and faq_has_li
            else:
                faq_properly_formatted = False
            print(f"✅ FAQ правильно отформатирован: {'ДА' if faq_properly_formatted else 'НЕТ'}")
            
            # 5. Проверяем отсутствие лишних элементов
            bad_elements = ['**Описание:**', '**Статья:**', '**Краткое описание:**', 'Размер текста:', 'A-', 'A+']
            found_bad = [elem for elem in bad_elements if elem in content]
            print(f"❌ Лишние элементы: {len(found_bad)}/{len(bad_elements)} - {found_bad}")
            
            # 6. Проверяем HTML-теги
            html_tags = ['<h2>Введение</h2>', '<h2>Анализ причин</h2>', '<h2>Практические техники</h2>', '<h2>Профилактика и выводы</h2>', '<h2>Часто задаваемые вопросы</h2>']
            found_tags = [tag for tag in html_tags if tag in content]
            print(f"✅ HTML-заголовки: {len(found_tags)}/{len(html_tags)} - {found_tags}")
            
            # 7. Проверяем описание в начале
            has_description = '<div class="article-description">' in content
            print(f"✅ Описание в начале: {'ДА' if has_description else 'НЕТ'}")
            
            # Показываем фрагменты
            print("\n📄 ФРАГМЕНТЫ СТАТЬИ:")
            print("-" * 50)
            print("НАЧАЛО СТАТЬИ (первые 500 символов):")
            print(content[:500] + "...")
            
            # Показываем FAQ раздел
            if faq_start != -1:
                print("\nFAQ РАЗДЕЛ:")
                faq_end = content.find('</ul>', faq_start) + 5
                if faq_end > faq_start:
                    print(content[faq_start:faq_end])
                else:
                    print(content[faq_start:faq_start + 800] + "...")
            
            # Показываем практические техники
            tech_start = content.find('<h2>Практические техники</h2>')
            if tech_start != -1:
                print("\nПРАКТИЧЕСКИЕ ТЕХНИКИ:")
                tech_end = content.find('<h2>', tech_start + 1)
                if tech_end == -1:
                    tech_end = tech_start + 800
                print(content[tech_start:tech_end] + "...")
            
            # Общая оценка финальных исправлений
            fixes_score = 0
            if has_title:
                fixes_score += 2  # Есть заголовок
            if not title_in_desc:
                fixes_score += 2  # Краткое описание без названия
            if has_ul_tags and has_li_tags:
                fixes_score += 2  # Маркерованные списки
            if faq_properly_formatted:
                fixes_score += 2  # FAQ правильно отформатирован
            if len(found_bad) == 0:
                fixes_score += 1  # Нет лишних элементов
            if len(found_tags) >= 4:
                fixes_score += 1  # Правильные HTML-заголовки
            
            print(f"\n🎯 ОЦЕНКА ФИНАЛЬНЫХ ИСПРАВЛЕНИЙ: {fixes_score}/10")
            
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
            print(f"❌ Было: Нет названия - {'ИСПРАВЛЕНО' if has_title else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Краткое описание переписывает название - {'ИСПРАВЛЕНО' if not title_in_desc else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: FAQ в одну строку - {'ИСПРАВЛЕНО' if faq_properly_formatted else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Списки не маркерованные - {'ИСПРАВЛЕНО' if has_ul_tags and has_li_tags else 'НЕ ИСПРАВЛЕНО'}")
            print(f"✅ Стало: Чистая структура с правильным форматированием - {'ДА' if fixes_score >= 8 else 'НЕТ'}")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_final_fixes()
    sys.exit(0 if success else 1)
