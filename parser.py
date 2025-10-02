#!/usr/bin/env python3
"""
Модуль для парсинга статей с Psychology Today
"""

import requests
from bs4 import BeautifulSoup
import time
import logging
from typing import List, Dict, Optional
from urllib.parse import urljoin, urlparse
import re

class PsychologyTodayParser:
    def __init__(self):
        self.base_url = "https://www.psychologytoday.com/us"
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        })
        
        # Темы для игнорирования
        self.ignore_topics = [
            'neuroscience', 'neurotransmitters', 'politics', 'social movements',
            'business', 'management', 'leadership', 'ai', 'technology', 'metaverse'
        ]
        
        # Приоритетные темы
        self.priority_topics = [
            'sex', 'love', 'relationships', 'marriage', 'anxiety', 'depression', 
            'stress', 'self-help', 'mindfulness', 'personal growth', 'parenting', 
            'child development', 'family', 'motivation', 'habits', 'growth mindset'
        ]
        
    def get_article_links(self, max_articles: int = 20) -> List[str]:
        """Получить ссылки на статьи с главной страницы"""
        try:
            response = self.session.get(self.base_url, timeout=30)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'html.parser')
            links = []
            
            # Ищем ссылки на статьи в различных секциях
            selectors = [
                'a[href*="/blog/"]',
                'a[href*="/articles/"]', 
                'a[href*="/us/blog/"]',
                '.article-title a',
                '.blog-post a',
                '.content-item a'
            ]
            
            for selector in selectors:
                elements = soup.select(selector)
                for element in elements:
                    href = element.get('href')
                    if href and self._is_valid_article_link(href):
                        full_url = urljoin(self.base_url, href)
                        if full_url not in links:
                            links.append(full_url)
            
            logging.info(f"Найдено {len(links)} потенциальных статей")
            return links[:max_articles]
            
        except Exception as e:
            logging.error(f"Ошибка при получении ссылок: {e}")
            return []
    
    def _is_valid_article_link(self, href: str) -> bool:
        """Проверить, является ли ссылка валидной статьей"""
        if not href:
            return False
            
        # Исключаем служебные страницы
        exclude_patterns = [
            '/about', '/contact', '/privacy', '/terms', '/subscribe',
            '/newsletter', '/search', '/authors', '/categories'
        ]
        
        for pattern in exclude_patterns:
            if pattern in href.lower():
                return False
                
        # Должна содержать путь к статье
        return any(pattern in href.lower() for pattern in ['/blog/', '/articles/', '/us/blog/'])
    
    def parse_article(self, url: str) -> Optional[Dict]:
        """Парсить отдельную статью"""
        try:
            response = self.session.get(url, timeout=30)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Извлекаем заголовок
            title = self._extract_title(soup)
            if not title:
                return None
                
            # Извлекаем контент
            content = self._extract_content(soup)
            if not content or len(content) < 500:  # Минимум 500 символов
                return None
                
            # Извлекаем метаданные
            author = self._extract_author(soup)
            date = self._extract_date(soup)
            tags = self._extract_tags(soup)
            
            # Проверяем релевантность по тегам
            if not self._is_relevant_article(tags, title, content):
                return None
                
            return {
                'url': url,
                'title': title,
                'content': content,
                'author': author,
                'date': date,
                'tags': tags,
                'word_count': len(content.split())
            }
            
        except Exception as e:
            logging.error(f"Ошибка при парсинге статьи {url}: {e}")
            return None
    
    def _extract_title(self, soup: BeautifulSoup) -> Optional[str]:
        """Извлечь заголовок статьи"""
        selectors = [
            'h1.article-title',
            'h1.blog-title', 
            'h1.entry-title',
            'h1',
            '.article-header h1',
            '.blog-header h1'
        ]
        
        for selector in selectors:
            element = soup.select_one(selector)
            if element:
                title = element.get_text().strip()
                if title and len(title) > 10:
                    return title
        return None
    
    def _extract_content(self, soup: BeautifulSoup) -> Optional[str]:
        """Извлечь основной контент статьи"""
        selectors = [
            '.article-content',
            '.blog-content',
            '.entry-content',
            '.post-content',
            '.content-body',
            'article .content'
        ]
        
        for selector in selectors:
            element = soup.select_one(selector)
            if element:
                # Удаляем скрипты и стили
                for script in element(["script", "style"]):
                    script.decompose()
                
                content = element.get_text()
                # Очищаем от лишних пробелов
                content = re.sub(r'\s+', ' ', content).strip()
                
                if content and len(content) > 500:
                    return content
        return None
    
    def _extract_author(self, soup: BeautifulSoup) -> Optional[str]:
        """Извлечь автора статьи"""
        selectors = [
            '.author-name',
            '.byline',
            '.article-author',
            '.blog-author',
            '[rel="author"]'
        ]
        
        for selector in selectors:
            element = soup.select_one(selector)
            if element:
                return element.get_text().strip()
        return None
    
    def _extract_date(self, soup: BeautifulSoup) -> Optional[str]:
        """Извлечь дату публикации"""
        selectors = [
            '.publish-date',
            '.article-date',
            '.blog-date',
            'time[datetime]',
            '.date'
        ]
        
        for selector in selectors:
            element = soup.select_one(selector)
            if element:
                # Пытаемся получить datetime атрибут
                datetime_attr = element.get('datetime')
                if datetime_attr:
                    return datetime_attr
                return element.get_text().strip()
        return None
    
    def _extract_tags(self, soup: BeautifulSoup) -> List[str]:
        """Извлечь теги статьи"""
        tags = []
        
        # Ищем теги в различных местах
        tag_selectors = [
            '.tags a',
            '.article-tags a',
            '.blog-tags a',
            '.categories a',
            '.topics a'
        ]
        
        for selector in tag_selectors:
            elements = soup.select(selector)
            for element in elements:
                tag = element.get_text().strip().lower()
                if tag and tag not in tags:
                    tags.append(tag)
        
        return tags
    
    def _is_relevant_article(self, tags: List[str], title: str, content: str) -> bool:
        """Проверить релевантность статьи по темам"""
        text_to_check = ' '.join(tags + [title, content[:1000]]).lower()
        
        # Проверяем, есть ли игнорируемые темы
        for ignore_topic in self.ignore_topics:
            if ignore_topic in text_to_check:
                return False
        
        # Проверяем, есть ли приоритетные темы
        for priority_topic in self.priority_topics:
            if priority_topic in text_to_check:
                return True
        
        # Если нет явных приоритетных тем, но статья психологическая - тоже подходит
        psychology_keywords = ['psychology', 'mental', 'therapy', 'counseling', 'behavior', 'emotion']
        return any(keyword in text_to_check for keyword in psychology_keywords)
    
    def get_relevant_articles(self, max_articles: int = 3) -> List[Dict]:
        """Получить релевантные статьи"""
        try:
            # Получаем ссылки
            links = self.get_article_links(max_articles * 3)  # Берем больше, чтобы отфильтровать
            
            articles = []
            for link in links:
                article = self.parse_article(link)
                if article:
                    articles.append(article)
                    if len(articles) >= max_articles:
                        break
                time.sleep(1)  # Пауза между запросами
            
            logging.info(f"Получено {len(articles)} релевантных статей")
            return articles
            
        except Exception as e:
            logging.error(f"Ошибка при получении релевантных статей: {e}")
            return []

if __name__ == "__main__":
    # Тестирование парсера
    logging.basicConfig(level=logging.INFO)
    parser = PsychologyTodayParser()
    articles = parser.get_relevant_articles(3)
    
    for i, article in enumerate(articles, 1):
        print(f"\n--- Статья {i} ---")
        print(f"Заголовок: {article['title']}")
        print(f"Автор: {article['author']}")
        print(f"Теги: {', '.join(article['tags'])}")
        print(f"Слов: {article['word_count']}")
        print(f"URL: {article['url']}")
