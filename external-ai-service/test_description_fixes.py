#!/usr/bin/env python3
"""
Тест исправлений краткого описания для карточки статьи
"""

import os
import sys
from article_writer import ArticleWriter

def test_description_fixes():
    """Тестируем исправления краткого описания"""
    
    # Проверяем наличие API ключа
    if not os.getenv('OPENAI_API_KEY'):
        print("❌ OPENAI_API_KEY не установлен")
        return False
    
    # Тестовые данные
    test_analysis = {
        'main_theme': 'Визуализация для снятия стресса',
        'main_message': 'Техники визуализации помогают быстро снять стресс и тревогу',
        'interesting_facts': [
            'Визуализация активирует те же участки мозга, что и реальные действия',
            'Ментальные образы влияют на физиологические процессы',
            'Визуализация помогает снизить уровень кортизола'
        ],
        'hidden_truths': [
            'Не все техники визуализации одинаково эффективны',
            'Визуализация требует регулярной практики',
            'Ментальные образы могут быть адаптированы под личные предпочтения'
        ],
        'practical_advice': [
            'Визуализация пляжа для расслабления',
            'Техника безопасного места',
            'Визуализация успеха для мотивации'
        ]
    }
    
    print("🧪 ТЕСТ ИСПРАВЛЕНИЙ КРАТКОГО ОПИСАНИЯ")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест улучшенной генерации с исправлениями описания
        print("\n📝 Генерация статьи с исправлениями описания...")
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
            print(f"📄 Краткое описание: {short_desc}")
            
            # Анализ исправлений описания
            print("\n🔍 АНАЛИЗ ИСПРАВЛЕНИЙ ОПИСАНИЯ:")
            print("-" * 50)
            
            content = article['content']
            
            # 1. Проверяем отсутствие "Пример" в кратком описании
            no_example_in_desc = 'пример' not in short_desc.lower()
            print(f"✅ Нет 'Пример' в описании: {'ДА' if no_example_in_desc else 'НЕТ'}")
            
            # 2. Проверяем отсутствие "Введение" в кратком описании
            no_introduction_in_desc = 'введение' not in short_desc.lower()
            print(f"✅ Нет 'Введение' в описании: {'ДА' if no_introduction_in_desc else 'НЕТ'}")
            
            # 3. Проверяем отсутствие звездочек в кратком описании
            no_asterisks_in_desc = '**' not in short_desc
            print(f"✅ Нет звездочек в описании: {'ДА' if no_asterisks_in_desc else 'НЕТ'}")
            
            # 4. Проверяем длину краткого описания (до 150 символов)
            desc_length_ok = len(short_desc) <= 150
            print(f"✅ Длина описания OK (≤150): {'ДА' if desc_length_ok else 'НЕТ'} - {len(short_desc)} символов")
            
            # 5. Проверяем, что описание начинается с содержательного текста
            desc_starts_well = not short_desc.startswith(('**', 'Пример', 'Введение', '*'))
            print(f"✅ Описание начинается хорошо: {'ДА' if desc_starts_well else 'НЕТ'}")
            
            # 6. Проверяем наличие заголовка H1
            has_h1 = '<h1>' in content and '</h1>' in content
            print(f"✅ Заголовок H1: {'ЕСТЬ' if has_h1 else 'НЕТ'}")
            
            # 7. Проверяем отсутствие лишних элементов
            bad_elements = ['**Описание:**', '**Статья:**', '**Краткое описание:**', 'Размер текста:', 'A-', 'A+']
            found_bad = [elem for elem in bad_elements if elem in content]
            print(f"❌ Лишние элементы: {len(found_bad)}/{len(bad_elements)} - {found_bad}")
            
            # Показываем фрагменты
            print("\n📄 ФРАГМЕНТЫ СТАТЬИ:")
            print("-" * 50)
            print("НАЧАЛО СТАТЬИ (первые 500 символов):")
            print(content[:500] + "...")
            
            # Показываем практические техники
            tech_start = content.find('<h2>Практические техники</h2>')
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
            
            # Общая оценка исправлений описания
            desc_score = 0
            if no_example_in_desc:
                desc_score += 2  # Нет "Пример" в описании
            if no_introduction_in_desc:
                desc_score += 2  # Нет "Введение" в описании
            if no_asterisks_in_desc:
                desc_score += 2  # Нет звездочек в описании
            if desc_length_ok:
                desc_score += 2  # Длина описания OK
            if desc_starts_well:
                desc_score += 2  # Описание начинается хорошо
            
            print(f"\n🎯 ОЦЕНКА ИСПРАВЛЕНИЙ ОПИСАНИЯ: {desc_score}/10")
            
            if desc_score >= 9:
                print("🌟 ОТЛИЧНО! Описание идеально подходит для карточки статьи")
            elif desc_score >= 7:
                print("👍 ХОРОШО! Описание хорошо подходит для карточки")
            elif desc_score >= 5:
                print("⚠️ УДОВЛЕТВОРИТЕЛЬНО! Описание требует небольших доработок")
            else:
                print("❌ ПЛОХО! Описание требует серьезных исправлений")
            
            # Сравнение с предыдущими проблемами
            print(f"\n📊 СРАВНЕНИЕ С ПРЕДЫДУЩИМИ ПРОБЛЕМАМИ:")
            print("-" * 50)
            print(f"❌ Было: '*Пример*:' в описании - {'ИСПРАВЛЕНО' if no_example_in_desc else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: 'Введение' в описании - {'ИСПРАВЛЕНО' if no_introduction_in_desc else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Звездочки в описании - {'ИСПРАВЛЕНО' if no_asterisks_in_desc else 'НЕ ИСПРАВЛЕНО'}")
            print(f"❌ Было: Служебные элементы в описании - {'ИСПРАВЛЕНО' if desc_starts_well else 'НЕ ИСПРАВЛЕНО'}")
            print(f"✅ Стало: Чистое описание для карточки статьи - {'ДА' if desc_score >= 8 else 'НЕТ'}")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_description_fixes()
    sys.exit(0 if success else 1)
