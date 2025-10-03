#!/usr/bin/env python3
"""
Новый AI процессор для ежедневного создания адаптированных психологических статей
на основе контента с Psychology Today
"""

import os
import json
import logging
import time
from datetime import datetime
from typing import List, Dict, Optional
from dotenv import load_dotenv

# Импортируем наши модули
from parser import PsychologyTodayParser
from content_analyzer import ContentAnalyzer
from article_writer import ArticleWriter
from image_prompter import ImagePrompter
from duplicate_tracker import DuplicateTracker

load_dotenv()

class PsychologyArticleProcessor:
    def __init__(self):
        # Инициализируем компоненты
        self.parser = PsychologyTodayParser()
        self.analyzer = ContentAnalyzer()
        self.writer = ArticleWriter()
        self.image_prompter = ImagePrompter()
        self.duplicate_tracker = DuplicateTracker()
        
        # Настройки
        self.max_articles_per_day = 3
        self.min_word_count = 12000  # Минимум 12,000 символов без пробелов
        self.max_word_count = 18000  # Максимум 18,000 символов без пробелов
        
        # Настройка логирования
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler('psychology_processor.log'),
                logging.StreamHandler()
            ]
        )
    
    def process_daily_articles(self) -> List[Dict]:
        """Основной метод для ежедневной обработки статей"""
        logging.info("🚀 Начинаю ежедневную обработку статей")
        
        try:
            # 1. Парсинг и фильтрация статей
            logging.info("📰 Этап 1: Парсинг статей с Psychology Today")
            raw_articles = self.parser.get_relevant_articles(self.max_articles_per_day * 2)
            
            if not raw_articles:
                logging.warning("❌ Не найдено релевантных статей")
                return []
            
            # 2. Фильтрация по использованным ссылкам
            logging.info("🔍 Этап 2: Фильтрация новых статей")
            new_articles = []
            for article in raw_articles:
                if not self.duplicate_tracker.is_used(article['url']):
                    new_articles.append(article)
                else:
                    logging.info(f"⏭️ Пропускаю уже использованную статью: {article['title'][:50]}...")
            
            if not new_articles:
                logging.warning("❌ Все статьи уже использованы")
                return []
            
            # Берем только нужное количество
            new_articles = new_articles[:self.max_articles_per_day]
            logging.info(f"✅ Отобрано {len(new_articles)} новых статей для обработки")
            
            # 3. Анализ статей дешевой моделью
            logging.info("🧠 Этап 3: Анализ статей")
            analyses = self.analyzer.analyze_multiple_articles(new_articles)
            
            if not analyses:
                logging.error("❌ Не удалось проанализировать статьи")
                return []
            
            # 4. Создание адаптированных статей
            logging.info("✍️ Этап 4: Написание адаптированных статей")
            articles = self.writer.write_multiple_articles(analyses)
            
            if not articles:
                logging.error("❌ Не удалось написать статьи")
                return []
            
            # 5. Фильтрация по объему
            logging.info("📏 Этап 5: Проверка объема статей")
            filtered_articles = []
            for article in articles:
                word_count = article['word_count']
                if self.min_word_count <= word_count <= self.max_word_count:
                    filtered_articles.append(article)
                    logging.info(f"✅ Статья '{article['title'][:50]}...' - {word_count} символов")
                else:
                    logging.warning(f"⚠️ Статья '{article['title'][:50]}...' не подходит по объему: {word_count} символов")
            
            if not filtered_articles:
                logging.error("❌ Нет статей подходящего объема")
                return []
            
            # 6. Подбор изображений
            logging.info("🖼️ Этап 6: Подбор изображений")
            articles_with_images = self.image_prompter.get_images_for_articles(filtered_articles)
            
            # 7. Помечаем исходные ссылки как использованные
            logging.info("📝 Этап 7: Обновление списка использованных ссылок")
            used_urls = [analysis['original_url'] for analysis in analyses]
            self.duplicate_tracker.mark_multiple_as_used(used_urls)
            
            # 8. Добавляем метаданные
            for article in articles_with_images:
                article['created_at'] = datetime.now().isoformat()
                article['source'] = 'Psychology Today'
                article['processing_date'] = datetime.now().strftime('%Y-%m-%d')
            
            logging.info(f"🎉 Успешно обработано {len(articles_with_images)} статей")
            return articles_with_images
            
        except Exception as e:
            logging.error(f"❌ Критическая ошибка при обработке статей: {e}")
            return []
    
    def save_articles_to_file(self, articles: List[Dict], filename: str = None) -> str:
        """Сохранить статьи в файл"""
        if filename is None:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"psychology_articles_{timestamp}.json"
        
        try:
            data = {
                'generated_at': datetime.now().isoformat(),
                'total_articles': len(articles),
                'articles': articles
            }
            
            with open(filename, 'w', encoding='utf-8') as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
            
            logging.info(f"💾 Статьи сохранены в файл: {filename}")
            return filename
            
        except Exception as e:
            logging.error(f"❌ Ошибка при сохранении статей: {e}")
            return None
    
    def get_processing_stats(self) -> Dict:
        """Получить статистику обработки"""
        duplicate_stats = self.duplicate_tracker.get_stats()
        
        return {
            'duplicate_tracker': duplicate_stats,
            'max_articles_per_day': self.max_articles_per_day,
            'word_count_range': f"{self.min_word_count}-{self.max_word_count}",
            'components_initialized': {
                'parser': self.parser is not None,
                'analyzer': self.analyzer is not None,
                'writer': self.writer is not None,
                'image_prompter': self.image_prompter is not None,
                'duplicate_tracker': self.duplicate_tracker is not None
            }
        }
    
    def run_daily_processing(self):
        """Запустить ежедневную обработку"""
        logging.info("🌅 Запуск ежедневной обработки статей")
        
        # Получаем статистику
        stats = self.get_processing_stats()
        logging.info(f"📊 Статистика: {json.dumps(stats, ensure_ascii=False, indent=2)}")
        
        # Обрабатываем статьи
        articles = self.process_daily_articles()
        
        if articles:
            # Сохраняем в файл
            filename = self.save_articles_to_file(articles)
            
            # Выводим краткий отчет
            logging.info("📋 ОТЧЕТ ОБ ОБРАБОТКЕ:")
            for i, article in enumerate(articles, 1):
                logging.info(f"  {i}. {article['title']}")
                logging.info(f"     Категория: {article['category']}")
                logging.info(f"     Слов: {article['word_count']}")
                logging.info(f"     Изображение: {'✅' if article.get('featured_image') else '❌'}")
                logging.info(f"     Теги: {', '.join(article['tags'])}")
            
            logging.info(f"💾 Все статьи сохранены в: {filename}")
            return articles
        else:
            logging.warning("⚠️ Статьи не были обработаны")
            return []

def main():
    """Точка входа"""
    processor = PsychologyArticleProcessor()
    
    # Проверяем переменные окружения
    required_env_vars = ['OPENAI_API_KEY']
    missing_vars = [var for var in required_env_vars if not os.getenv(var)]
    
    if missing_vars:
        logging.error(f"❌ Отсутствуют переменные окружения: {', '.join(missing_vars)}")
        return
    
    # Запускаем обработку
    articles = processor.run_daily_processing()
    
    if articles:
        print(f"\n🎉 Успешно обработано {len(articles)} статей!")
        print("📁 Статьи сохранены в JSON файл")
    else:
        print("\n⚠️ Статьи не были обработаны")
        print("📋 Проверьте логи для подробностей")

if __name__ == "__main__":
    main()
