#!/usr/bin/env python3
"""
Тест форматирования статей
"""

import os
import sys
from article_writer import ArticleWriter

def test_article_formatting():
    """Тестируем форматирование статей"""
    
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
    
    print("🧪 ТЕСТ ФОРМАТИРОВАНИЯ СТАТЕЙ")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест улучшенной генерации с форматированием
        print("\n📝 Генерация статьи с форматированием...")
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
            
            # Проверяем наличие новых полей
            print(f"\n📋 НОВЫЕ ПОЛЯ:")
            print(f"📄 Краткое описание: {article.get('short_description', 'НЕТ')[:100]}...")
            print(f"📄 Excerpt: {article.get('excerpt', 'НЕТ')[:100]}...")
            print(f"📄 Meta description: {article.get('meta_description', 'НЕТ')[:100]}...")
            
            # Анализ форматирования
            print("\n🔍 АНАЛИЗ ФОРМАТИРОВАНИЯ:")
            print("-" * 40)
            
            content = article['content']
            
            # Проверяем HTML-теги
            html_tags = ['<h2>', '<p>', '<div>', '<ul>', '<li>']
            found_tags = [tag for tag in html_tags if tag in content]
            print(f"✅ HTML-теги: {len(found_tags)}/{len(html_tags)} - {found_tags}")
            
            # Проверяем отсутствие лишних символов
            bad_symbols = ['**Статья:**', '**Введение:**', '**Анализ причин:**']
            found_bad = [symbol for symbol in bad_symbols if symbol in content]
            print(f"❌ Лишние символы: {len(found_bad)}/{len(bad_symbols)} - {found_bad}")
            
            # Проверяем структуру
            structure_elements = ['<h2>Введение</h2>', '<h2>Анализ причин</h2>', '<h2>Практические техники</h2>']
            found_structure = [elem for elem in structure_elements if elem in content]
            print(f"✅ Структура: {len(found_structure)}/{len(structure_elements)} - {found_structure}")
            
            # Проверяем описание в начале
            has_description = '<div class="article-description">' in content
            print(f"✅ Описание в начале: {'ДА' if has_description else 'НЕТ'}")
            
            # Проверяем FAQ
            has_faq = 'FAQ' in content or 'Часто задаваемые вопросы' in content
            print(f"✅ FAQ раздел: {'ДА' if has_faq else 'НЕТ'}")
            
            # Показываем фрагменты
            print("\n📄 ФРАГМЕНТЫ СТАТЬИ:")
            print("-" * 40)
            print("НАЧАЛО СТАТЬИ (первые 500 символов):")
            print(content[:500] + "...")
            
            print("\nКОНЕЦ СТАТЬИ (последние 300 символов):")
            print("..." + content[-300:])
            
            # Общая оценка форматирования
            formatting_score = 0
            if len(found_tags) >= 3:
                formatting_score += 2
            if len(found_bad) == 0:
                formatting_score += 2
            if len(found_structure) >= 2:
                formatting_score += 2
            if has_description:
                formatting_score += 2
            if has_faq:
                formatting_score += 2
            
            print(f"\n🎯 ОЦЕНКА ФОРМАТИРОВАНИЯ: {formatting_score}/10")
            
            if formatting_score >= 8:
                print("🌟 ОТЛИЧНО! Статья отлично отформатирована")
            elif formatting_score >= 6:
                print("👍 ХОРОШО! Статья хорошо отформатирована")
            else:
                print("⚠️ ТРЕБУЕТ УЛУЧШЕНИЯ")
                
            return True
            
        else:
            print("❌ Ошибка генерации статьи")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_article_formatting()
    sys.exit(0 if success else 1)
