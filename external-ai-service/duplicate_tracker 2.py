#!/usr/bin/env python3
"""
Модуль для отслеживания использованных ссылок и предотвращения дубликатов
"""

import os
import json
import logging
from typing import List, Set
from datetime import datetime, timedelta

class DuplicateTracker:
    def __init__(self, used_links_file: str = "used_links.json"):
        self.used_links_file = used_links_file
        self.used_links = self._load_used_links()
        
    def _load_used_links(self) -> Set[str]:
        """Загрузить список использованных ссылок"""
        if not os.path.exists(self.used_links_file):
            return set()
        
        try:
            with open(self.used_links_file, 'r', encoding='utf-8') as f:
                data = json.load(f)
                return set(data.get('used_links', []))
        except Exception as e:
            logging.error(f"Ошибка при загрузке использованных ссылок: {e}")
            return set()
    
    def _save_used_links(self):
        """Сохранить список использованных ссылок"""
        try:
            data = {
                'used_links': list(self.used_links),
                'last_updated': datetime.now().isoformat(),
                'total_links': len(self.used_links)
            }
            
            with open(self.used_links_file, 'w', encoding='utf-8') as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
                
        except Exception as e:
            logging.error(f"Ошибка при сохранении использованных ссылок: {e}")
    
    def is_used(self, url: str) -> bool:
        """Проверить, использовалась ли ссылка ранее"""
        return url in self.used_links
    
    def mark_as_used(self, url: str):
        """Пометить ссылку как использованную"""
        self.used_links.add(url)
        self._save_used_links()
        logging.info(f"Ссылка помечена как использованная: {url}")
    
    def mark_multiple_as_used(self, urls: List[str]):
        """Пометить несколько ссылок как использованные"""
        for url in urls:
            self.used_links.add(url)
        self._save_used_links()
        logging.info(f"Помечено {len(urls)} ссылок как использованные")
    
    def filter_new_links(self, links: List[str]) -> List[str]:
        """Отфильтровать только новые ссылки"""
        new_links = [link for link in links if not self.is_used(link)]
        logging.info(f"Из {len(links)} ссылок {len(new_links)} новых")
        return new_links
    
    def get_stats(self) -> dict:
        """Получить статистику использованных ссылок"""
        return {
            'total_used_links': len(self.used_links),
            'file_exists': os.path.exists(self.used_links_file),
            'file_size': os.path.getsize(self.used_links_file) if os.path.exists(self.used_links_file) else 0
        }
    
    def cleanup_old_links(self, days_to_keep: int = 30):
        """Очистить старые ссылки (опционально)"""
        # В текущей реализации мы не храним даты, поэтому просто логируем
        logging.info(f"Очистка ссылок старше {days_to_keep} дней не реализована в текущей версии")
    
    def export_used_links(self, export_file: str = None) -> str:
        """Экспортировать использованные ссылки в файл"""
        if export_file is None:
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            export_file = f"used_links_export_{timestamp}.json"
        
        try:
            data = {
                'export_date': datetime.now().isoformat(),
                'total_links': len(self.used_links),
                'used_links': list(self.used_links)
            }
            
            with open(export_file, 'w', encoding='utf-8') as f:
                json.dump(data, f, ensure_ascii=False, indent=2)
            
            logging.info(f"Использованные ссылки экспортированы в {export_file}")
            return export_file
            
        except Exception as e:
            logging.error(f"Ошибка при экспорте ссылок: {e}")
            return None
    
    def import_used_links(self, import_file: str) -> bool:
        """Импортировать использованные ссылки из файла"""
        if not os.path.exists(import_file):
            logging.error(f"Файл для импорта не найден: {import_file}")
            return False
        
        try:
            with open(import_file, 'r', encoding='utf-8') as f:
                data = json.load(f)
            
            imported_links = set(data.get('used_links', []))
            self.used_links.update(imported_links)
            self._save_used_links()
            
            logging.info(f"Импортировано {len(imported_links)} ссылок из {import_file}")
            return True
            
        except Exception as e:
            logging.error(f"Ошибка при импорте ссылок: {e}")
            return False

if __name__ == "__main__":
    # Тестирование трекера дубликатов
    logging.basicConfig(level=logging.INFO)
    
    tracker = DuplicateTracker("test_used_links.json")
    
    # Тестовые ссылки
    test_links = [
        "https://example.com/article1",
        "https://example.com/article2",
        "https://example.com/article3"
    ]
    
    print("Тестирование трекера дубликатов:")
    
    # Проверяем новые ссылки
    new_links = tracker.filter_new_links(test_links)
    print(f"Новые ссылки: {new_links}")
    
    # Помечаем как использованные
    tracker.mark_multiple_as_used(test_links[:2])
    
    # Проверяем снова
    new_links = tracker.filter_new_links(test_links)
    print(f"Новые ссылки после пометки: {new_links}")
    
    # Статистика
    stats = tracker.get_stats()
    print(f"Статистика: {stats}")
    
    # Экспорт
    export_file = tracker.export_used_links()
    print(f"Экспорт в файл: {export_file}")
    
    # Очистка тестового файла
    if os.path.exists("test_used_links.json"):
        os.remove("test_used_links.json")
    if export_file and os.path.exists(export_file):
        os.remove(export_file)
