<?php
// Минимальная версия API для тестирования
header('Content-Type: application/json; charset=utf-8');

// Простой тест без сложной логики
$testResponse = [
  'success' => true,
  'message' => 'API is working',
  'badges' => [
    [
      'id' => 1,
      'name' => 'Тест',
      'slug' => 'test',
      'color' => '#ffffff',
      'background_color' => '#007bff'
    ]
  ]
];

echo json_encode($testResponse, JSON_UNESCAPED_UNICODE);
?>