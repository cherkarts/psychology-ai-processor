#!/usr/bin/env python3
"""
Тест новой функции write_adapted_article_quality
"""

import os
import sys
from article_writer import ArticleWriter

def test_quality_generation():
    """Тестируем новую функцию качества"""
    
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
    
    print("🧪 ТЕСТ НОВОЙ ФУНКЦИИ КАЧЕСТВА")
    print("=" * 50)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        print("\n📝 Генерация статьи с акцентом на качество...")
        article = writer.write_adapted_article_quality(test_analysis)
        
        if article:
            print("\n✅ СТАТЬЯ УСПЕШНО СГЕНЕРИРОВАНА!")
            print("=" * 50)
            print(f"📰 Заголовок: {article['title']}")
            print(f"📊 Длина: {len(article['content'])} символов")
            print(f"🏷️ Категория: {article['category']}")
            print(f"🔖 Теги: {', '.join(article['tags'])}")
            
            print("\n📄 СОДЕРЖАНИЕ СТАТЬИ:")
            print("-" * 50)
            print(article['content'][:500] + "..." if len(article['content']) > 500 else article['content'])
            
            # Анализ качества
            print("\n🔍 АНАЛИЗ КАЧЕСТВА:")
            print("-" * 30)
            
            content = article['content'].lower()
            
            # Проверяем наличие конкретных техник
            techniques = ['4-7-8', 'прогрессивная', 'дыхательные', 'медитация']
            found_techniques = [t for t in techniques if t in content]
            print(f"✅ Конкретные техники: {len(found_techniques)}/{len(techniques)} - {found_techniques}")
            
            # Проверяем наличие статистики
            stats_words = ['90%', '25%', 'процент', 'статистика', 'исследования']
            found_stats = [s for s in stats_words if s in content]
            print(f"✅ Статистика и факты: {len(found_stats)}/{len(stats_words)} - {found_stats}")
            
            # Проверяем структуру
            structure_words = ['введение', 'анализ', 'техники', 'выводы', 'заключение']
            found_structure = [s for s in structure_words if s in content]
            print(f"✅ Структура: {len(found_structure)}/{len(structure_words)} - {found_structure}")
            
            # Проверяем отсутствие общих фраз
            bad_phrases = ['важно помнить', 'следует отметить', 'необходимо понимать']
            found_bad = [p for p in bad_phrases if p in content]
            print(f"❌ Общие фразы: {len(found_bad)}/{len(bad_phrases)} - {found_bad}")
            
            # Общая оценка
            quality_score = 0
            if len(found_techniques) >= 2:
                quality_score += 2
            if len(found_stats) >= 1:
                quality_score += 2
            if len(found_structure) >= 3:
                quality_score += 2
            if len(found_bad) == 0:
                quality_score += 2
            if len(article['content']) >= 3000:
                quality_score += 2
            
            print(f"\n🎯 ОБЩАЯ ОЦЕНКА КАЧЕСТВА: {quality_score}/10")
            
            if quality_score >= 8:
                print("🌟 ОТЛИЧНО! Статья высокого качества")
            elif quality_score >= 6:
                print("👍 ХОРОШО! Статья хорошего качества")
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
    success = test_quality_generation()
    sys.exit(0 if success else 1)
