#!/usr/bin/env python3
"""
Демо процессор для генерации и публикации статей с исправлениями
"""

import os
import json
import logging
from datetime import datetime
from dotenv import load_dotenv

# Импортируем наши исправленные модули
from content_analyzer import ContentAnalyzer
from article_writer import ArticleWriter
from image_prompter import ImagePrompter
from integration_script import SiteIntegration

load_dotenv()

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('demo_processor.log'),
        logging.StreamHandler()
    ]
)

def create_demo_articles():
    """Создать демо статьи с исправлениями"""
    print("🎭 Создание демо статей с исправлениями...")
    
    # Инициализируем компоненты
    analyzer = ContentAnalyzer()
    writer = ArticleWriter()
    image_prompter = ImagePrompter()
    
    # Демо данные для анализа
    demo_analyses = [
        {
            'main_theme': 'Стресс и тревога в современном мире',
            'core_narrative': 'Как люди справляются с постоянным стрессом',
            'main_message': 'Стресс можно контролировать с помощью правильных техник',
            'emotional_tone': 'поддерживающий',
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
            ],
            'cultural_adaptation_notes': 'Учитывая особенности российского менталитета и отношение к психологии',
            'article_structure': {
                'introduction_approach': 'Начать с реальной истории из российской жизни',
                'problem_presentation': 'Показать, как стресс влияет на семью и работу',
                'solution_approach': 'Дать конкретные техники с объяснением',
                'conclusion_style': 'Мотивирующий призыв к действию'
            }
        }
    ]
    
    articles = []
    
    for i, analysis in enumerate(demo_analyses, 1):
        print(f"\n📝 Генерация статьи {i}/{len(demo_analyses)}: {analysis['main_theme']}")
        
        try:
            # Генерируем статью
            article_data = writer.write_adapted_article(analysis)
            
            if article_data:
                # Добавляем метаданные
                article_data['category'] = 'Психология'
                article_data['tags'] = ['психология', 'самопомощь', 'стресс', 'тревога']
                article_data['source'] = 'Psychology Today'
                article_data['processing_date'] = datetime.now().strftime('%Y-%m-%d')
                
                # Генерируем изображение
                print(f"🖼️ Поиск изображения для статьи {i}...")
                image_url = image_prompter.get_image_for_article(article_data)
                if image_url:
                    article_data['featured_image'] = image_url
                    print(f"✅ Изображение найдено: {image_url}")
                else:
                    print("⚠️ Изображение не найдено")
                
                articles.append(article_data)
                print(f"✅ Статья {i} сгенерирована успешно")
            else:
                print(f"❌ Ошибка генерации статьи {i}")
                
        except Exception as e:
            print(f"❌ Ошибка при генерации статьи {i}: {e}")
            logging.error(f"Ошибка генерации статьи {i}: {e}")
    
    return articles

def publish_articles(articles):
    """Публикация статей на сайт"""
    print(f"\n🚀 Публикация {len(articles)} статей на сайт...")
    
    site_url = os.getenv('SITE_URL', 'https://cherkas-therapy.ru')
    admin_api_key = os.getenv('ADMIN_API_KEY')
    integrator = SiteIntegration(site_url, admin_api_key)
    
    published_count = 0
    
    for i, article in enumerate(articles, 1):
        print(f"\n📤 Публикация статьи {i}/{len(articles)}: {article.get('title', 'Без названия')[:50]}...")
        
        try:
            if integrator.upload_article_to_site(article):
                published_count += 1
                print(f"✅ Статья {i} опубликована успешно")
            else:
                print(f"❌ Ошибка публикации статьи {i}")
        except Exception as e:
            print(f"❌ Ошибка при публикации статьи {i}: {e}")
            logging.error(f"Ошибка публикации статьи {i}: {e}")
    
    return published_count

def main():
    """Основная функция"""
    print("🎭 ДЕМО ПРОЦЕССОР С ИСПРАВЛЕНИЯМИ")
    print("=" * 50)
    
    try:
        # Создаем демо статьи
        articles = create_demo_articles()
        
        if not articles:
            print("❌ Не удалось создать демо статьи")
            return
        
        print(f"\n✅ Создано {len(articles)} демо статей")
        
        # Сохраняем в файл
        output_file = f"psychology_articles_demo_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump({
                'total_articles': len(articles),
                'generated_at': datetime.now().isoformat(),
                'articles': articles
            }, f, ensure_ascii=False, indent=2)
        
        print(f"💾 Статьи сохранены в файл: {output_file}")
        
        # Публикуем на сайт
        published_count = publish_articles(articles)
        
        print("\n" + "=" * 50)
        print("📊 ИТОГОВЫЙ ОТЧЕТ")
        print("=" * 50)
        print(f"📝 Всего статей создано: {len(articles)}")
        print(f"✅ Успешно опубликовано: {published_count}")
        print(f"❌ Ошибок публикации: {len(articles) - published_count}")
        
        if published_count == len(articles):
            print("🎉 ВСЕ СТАТЬИ УСПЕШНО СОЗДАНЫ И ОПУБЛИКОВАНЫ!")
        else:
            print("⚠️ Есть ошибки при публикации статей")
            
    except Exception as e:
        print(f"❌ Критическая ошибка: {e}")
        logging.error(f"Критическая ошибка: {e}")

if __name__ == "__main__":
    main()
