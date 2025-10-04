#!/usr/bin/env python3
"""
Тест улучшенной функции write_adapted_article_enhanced
Сравнение качества: обычная vs улучшенная генерация
"""

import os
import sys
from article_writer import ArticleWriter

def test_enhanced_quality():
    """Тестируем улучшенную функцию качества"""
    
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
    
    print("🧪 ТЕСТ УЛУЧШЕННОЙ ФУНКЦИИ КАЧЕСТВА")
    print("=" * 60)
    
    try:
        writer = ArticleWriter()
        print("✅ ArticleWriter инициализирован")
        
        # Тест 1: Обычная генерация качества
        print("\n📝 ТЕСТ 1: Обычная генерация качества...")
        article_quality = writer.write_adapted_article_quality(test_analysis)
        
        # Тест 2: Улучшенная генерация
        print("\n📝 ТЕСТ 2: Улучшенная генерация (создание + улучшение)...")
        article_enhanced = writer.write_adapted_article_enhanced(test_analysis)
        
        if article_quality and article_enhanced:
            print("\n✅ ОБЕ СТАТЬИ УСПЕШНО СГЕНЕРИРОВАНЫ!")
            print("=" * 60)
            
            # Сравнение характеристик
            print("\n📊 СРАВНЕНИЕ ХАРАКТЕРИСТИК:")
            print("-" * 40)
            print(f"📰 Заголовок (обычная): {article_quality['title']}")
            print(f"📰 Заголовок (улучшенная): {article_enhanced['title']}")
            print(f"📏 Длина (обычная): {len(article_quality['content'])} символов")
            print(f"📏 Длина (улучшенная): {len(article_enhanced['content'])} символов")
            print(f"📝 Слов (обычная): {article_quality['word_count']}")
            print(f"📝 Слов (улучшенная): {article_enhanced['word_count']}")
            
            # Анализ качества
            print("\n🔍 АНАЛИЗ КАЧЕСТВА:")
            print("-" * 40)
            
            def analyze_quality(content, name):
                content_lower = content.lower()
                
                # Проверяем наличие конкретных техник
                techniques = ['4-7-8', 'прогрессивная', 'дыхательные', 'медитация', 'релаксация']
                found_techniques = [t for t in techniques if t in content_lower]
                
                # Проверяем наличие статистики
                stats_words = ['90%', '25%', 'процент', 'статистика', 'исследования', 'данные']
                found_stats = [s for s in stats_words if s in content_lower]
                
                # Проверяем структуру
                structure_words = ['введение', 'анализ', 'техники', 'выводы', 'заключение', 'причины']
                found_structure = [s for s in structure_words if s in content_lower]
                
                # Проверяем отсутствие общих фраз
                bad_phrases = ['важно помнить', 'следует отметить', 'необходимо понимать', 'стоит отметить']
                found_bad = [p for p in bad_phrases if p in content_lower]
                
                # Проверяем наличие объяснений
                explanation_words = ['потому что', 'поэтому', 'механизм', 'причина', 'как работает', 'почему']
                found_explanations = [e for e in explanation_words if e in content_lower]
                
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
                if len(found_explanations) >= 3:
                    quality_score += 2
                
                print(f"\n{name}:")
                print(f"  ✅ Конкретные техники: {len(found_techniques)}/{len(techniques)} - {found_techniques}")
                print(f"  ✅ Статистика: {len(found_stats)}/{len(stats_words)} - {found_stats}")
                print(f"  ✅ Структура: {len(found_structure)}/{len(structure_words)} - {found_structure}")
                print(f"  ❌ Общие фразы: {len(found_bad)}/{len(bad_phrases)} - {found_bad}")
                print(f"  💡 Объяснения: {len(found_explanations)}/{len(explanation_words)} - {found_explanations}")
                print(f"  🎯 Оценка: {quality_score}/10")
                
                return quality_score
            
            score_quality = analyze_quality(article_quality['content'], "📝 ОБЫЧНАЯ ГЕНЕРАЦИЯ")
            score_enhanced = analyze_quality(article_enhanced['content'], "🌟 УЛУЧШЕННАЯ ГЕНЕРАЦИЯ")
            
            # Итоговое сравнение
            print("\n🏆 ИТОГОВОЕ СРАВНЕНИЕ:")
            print("=" * 40)
            print(f"📊 Обычная генерация: {score_quality}/10")
            print(f"📊 Улучшенная генерация: {score_enhanced}/10")
            
            if score_enhanced > score_quality:
                print(f"🎉 УЛУЧШЕНИЕ: +{score_enhanced - score_quality} баллов!")
            elif score_enhanced == score_quality:
                print("🤔 Качество одинаковое")
            else:
                print("⚠️ Улучшение не дало результата")
            
            # Показываем фрагменты для сравнения
            print("\n📄 ФРАГМЕНТЫ ДЛЯ СРАВНЕНИЯ:")
            print("-" * 40)
            print("ОБЫЧНАЯ (первые 300 символов):")
            print(article_quality['content'][:300] + "...")
            print("\nУЛУЧШЕННАЯ (первые 300 символов):")
            print(article_enhanced['content'][:300] + "...")
            
            return True
            
        else:
            print("❌ Ошибка генерации статей")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    success = test_enhanced_quality()
    sys.exit(0 if success else 1)
