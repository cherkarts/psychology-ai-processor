-- Обновляем изображения товаров
-- Товар ID 101 (qrwef)
UPDATE products SET 
    image = 'uploads/products/product_68ac3ac140bf2.jpg',
    gallery = '["uploads/products/gallery/gallery_68acd32c54d88.jpg","uploads/products/gallery/gallery_68ae0d3443c7b.jpg"]'
WHERE id = 101;

-- Товар ID 105 (фуа)
UPDATE products SET 
    image = 'uploads/products/product_68ac3ad3f19cf.jpg',
    gallery = '["uploads/products/gallery/gallery_68ae0d34441e2.jpg","uploads/products/gallery/gallery_68ae0d34442f9.jpg","uploads/products/gallery/gallery_68ae0d3444410.jpg"]'
WHERE id = 105;

-- Товар ID 106 (йсцв)
UPDATE products SET 
    image = 'uploads/products/product_68ac3b9de04c5.jpg',
    gallery = '["uploads/products/gallery/gallery_68b12ea89a21b.jpg","uploads/products/gallery/gallery_68b12ea89a2d6.jpg"]'
WHERE id = 106;

-- Проверяем результат
SELECT id, title, image, gallery FROM products WHERE id IN (101, 105, 106);
