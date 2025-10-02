<?php
/**
 * API для получения детальной информации об отзыве
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/auth.php';
require_once '../../includes/Database.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
  exit;
}

// Проверяем права доступа
if (!hasPermission('reviews')) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
  exit;
}

try {
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  $reviewId = intval($_GET['id'] ?? 0);

  if ($reviewId <= 0) {
    throw new Exception('Неверный ID отзыва');
  }

  // Получаем детальную информацию об отзыве
  $sql = "
        SELECT 
            pr.*,
            tu.telegram_username,
            tu.telegram_first_name,
            tu.telegram_last_name,
            tu.telegram_avatar,
            p.title as product_title,
            p.slug as product_slug,
            p.price as product_price,
            p.image as product_image,
            COALESCE(l.likes_count, 0) as likes_count,
            COALESCE(r.reports_count, 0) as reports_count
        FROM product_reviews pr
        LEFT JOIN telegram_users tu ON pr.telegram_user_id = tu.telegram_id
        LEFT JOIN products p ON pr.product_id = p.id
        LEFT JOIN (
            SELECT review_id, COUNT(*) as likes_count
            FROM product_review_likes
            GROUP BY review_id
        ) l ON pr.id = l.review_id
        LEFT JOIN (
            SELECT review_id, COUNT(*) as reports_count
            FROM product_review_reports
            GROUP BY review_id
        ) r ON pr.id = r.review_id
        WHERE pr.id = :review_id
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
  $stmt->execute();

  $review = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$review) {
    throw new Exception('Отзыв не найден');
  }

  // Получаем лайки
  $likesSql = "
        SELECT 
            prl.*,
            tu.telegram_username,
            tu.telegram_first_name,
            tu.telegram_last_name
        FROM product_review_likes prl
        LEFT JOIN telegram_users tu ON prl.telegram_user_id = tu.telegram_id
        WHERE prl.review_id = :review_id
        ORDER BY prl.created_at DESC
        LIMIT 10
    ";

  $likesStmt = $pdo->prepare($likesSql);
  $likesStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
  $likesStmt->execute();
  $likes = $likesStmt->fetchAll(PDO::FETCH_ASSOC);

  // Генерируем HTML
  $html = generateReviewDetailsHtml($review, $likes);

  echo json_encode([
    'success' => true,
    'html' => $html
  ]);

} catch (Exception $e) {
  error_log("Get product review details error: " . $e->getMessage());
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}

function generateReviewDetailsHtml($review, $likes)
{
  $avatar = $review['telegram_avatar'] ?? 'https://via.placeholder.com/60x60/6a7e9f/ffffff?text=' . substr($review['telegram_first_name'], 0, 1);

  $starsHtml = '';
  for ($i = 1; $i <= 5; $i++) {
    $starClass = $i <= $review['rating'] ? 'star filled' : 'star';
    $starsHtml .= "<span class=\"{$starClass}\">★</span>";
  }

  $statusLabels = [
    'pending' => 'На модерации',
    'approved' => 'Одобрено',
    'rejected' => 'Отклонено'
  ];
  $statusLabel = $statusLabels[$review['status']] ?? $review['status'];

  $statusClass = "status-{$review['status']}";

  $likesHtml = '';
  if (!empty($likes)) {
    $likesHtml = '<div class="likes-section">';
    $likesHtml .= '<h6>Лайки (' . count($likes) . '):</h6>';
    $likesHtml .= '<div class="likes-list">';
    foreach ($likes as $like) {
      $likesHtml .= '<div class="like-item">';
      $likesHtml .= '<span class="user-name">' . htmlspecialchars($like['telegram_first_name'] . ' ' . $like['telegram_last_name']) . '</span>';
      $likesHtml .= '<span class="user-username">@' . htmlspecialchars($like['telegram_username']) . '</span>';
      $likesHtml .= '<span class="like-date">' . date('d.m.Y H:i', strtotime($like['created_at'])) . '</span>';
      $likesHtml .= '</div>';
    }
    $likesHtml .= '</div>';
    $likesHtml .= '</div>';
  }

  return "
        <div class=\"review-details\">
            <div class=\"review-header\">
                <div class=\"review-user\">
                    <div class=\"user-avatar\">
                        <img src=\"{$avatar}\" alt=\"Avatar\" width=\"60\" height=\"60\">
                    </div>
                    <div class=\"user-info\">
                        <div class=\"user-name\">" . htmlspecialchars($review['telegram_first_name'] . ' ' . $review['telegram_last_name']) . "</div>
                        <div class=\"user-username\">@" . htmlspecialchars($review['telegram_username']) . "</div>
                        <div class=\"user-id\">ID: {$review['telegram_user_id']}</div>
                    </div>
                </div>
                <div class=\"review-meta\">
                    <div class=\"review-rating\">
                        {$starsHtml}
                        <span class=\"rating-value\">{$review['rating']}/5</span>
                    </div>
                    <div class=\"review-date\">" . date('d.m.Y H:i', strtotime($review['created_at'])) . "</div>
                    <div class=\"review-status {$statusClass}\">{$statusLabel}</div>
                </div>
            </div>

            <div class=\"product-section\">
                <h6>Товар:</h6>
                <div class=\"product-info\">
                    <div class=\"product-image\">
                        <img src=\"{$review['product_image']}\" alt=\"{$review['product_title']}\" width=\"80\" height=\"80\">
                    </div>
                    <div class=\"product-details\">
                        <div class=\"product-title\">" . htmlspecialchars($review['product_title']) . "</div>
                        <div class=\"product-price\">" . number_format($review['product_price'], 0, ',', ' ') . " ₽</div>
                        <div class=\"product-link\">
                            <a href=\"/product.php?slug={$review['product_slug']}\" target=\"_blank\">
                                Открыть товар
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class=\"review-content\">
                <h6>Текст отзыва:</h6>
                <div class=\"review-text\">" . nl2br(htmlspecialchars($review['text'])) . "</div>
            </div>

            <div class=\"review-stats\">
                <div class=\"stat-item\">
                    <i class=\"fas fa-heart\"></i>
                    <span>{$review['likes_count']} лайков</span>
                </div>
                <div class=\"stat-item\">
                    <i class=\"fas fa-flag\"></i>
                    <span>{$review['reports_count']} жалоб</span>
                </div>
            </div>

            {$likesHtml}

            " . (!empty($review['moderator_comment']) ? "
                <div class=\"moderator-comment\">
                    <h6>Комментарий модератора:</h6>
                    <p>" . nl2br(htmlspecialchars($review['moderator_comment'])) . "</p>
                </div>
            " : "") . "

            <div class=\"review-actions\">
                " . ($review['status'] === 'pending' ? "
                    <button class=\"btn btn-success btn-sm\" onclick=\"moderateReview({$review['id']}, 'approved')\">
                        <i class=\"fas fa-check\"></i> Одобрить
                    </button>
                    <button class=\"btn btn-danger btn-sm\" onclick=\"moderateReview({$review['id']}, 'rejected')\">
                        <i class=\"fas fa-times\"></i> Отклонить
                    </button>
                " : "") . "
            </div>
        </div>

        <style>
        .review-details {
            max-width: 100%;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .review-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar img {
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .user-username {
            color: #666;
            font-size: 14px;
        }
        
        .user-id {
            color: #999;
            font-size: 12px;
        }
        
        .review-meta {
            text-align: right;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
        }
        
        .star {
            color: #ddd;
            font-size: 18px;
        }
        
        .star.filled {
            color: #ffc107;
        }
        
        .rating-value {
            margin-left: 5px;
            font-weight: 600;
        }
        
        .review-date {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .review-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .product-section,
        .review-content {
            margin-bottom: 20px;
        }
        
        .product-section h6,
        .review-content h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .product-image img {
            border-radius: 8px;
            object-fit: cover;
        }
        
        .product-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .product-price {
            color: #28a745;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .product-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .product-link a:hover {
            text-decoration: underline;
        }
        
        .review-text {
            color: #333;
            line-height: 1.6;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .review-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px 0;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
        }
        
        .likes-section {
            margin-bottom: 20px;
        }
        
        .likes-section h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        
        .likes-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .like-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        
        .like-item .user-name {
            font-weight: 600;
            color: #333;
        }
        
        .like-item .user-username {
            color: #666;
            font-size: 12px;
        }
        
        .like-item .like-date {
            color: #999;
            font-size: 12px;
        }
        
        .moderator-comment {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        
        .moderator-comment h6 {
            color: #1976d2;
            margin-bottom: 8px;
        }
        
        .moderator-comment p {
            color: #333;
            margin: 0;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        </style>
    ";
}
?>
