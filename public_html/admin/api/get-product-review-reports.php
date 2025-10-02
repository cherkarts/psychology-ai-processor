<?php
/**
 * API для получения жалоб на отзыв
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

  $reviewId = intval($_GET['review_id'] ?? 0);

  if ($reviewId <= 0) {
    throw new Exception('Неверный ID отзыва');
  }

  // Получаем жалобы на отзыв
  $sql = "
        SELECT 
            prr.*,
            tu.telegram_username,
            tu.telegram_first_name,
            tu.telegram_last_name,
            tu.telegram_avatar
        FROM product_review_reports prr
        LEFT JOIN telegram_users tu ON prr.telegram_user_id = tu.telegram_id
        WHERE prr.review_id = :review_id
        ORDER BY prr.created_at DESC
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
  $stmt->execute();

  $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Получаем информацию об отзыве
  $reviewSql = "
        SELECT 
            pr.*,
            tu.telegram_username,
            tu.telegram_first_name,
            tu.telegram_last_name,
            p.title as product_title
        FROM product_reviews pr
        LEFT JOIN telegram_users tu ON pr.telegram_user_id = tu.telegram_id
        LEFT JOIN products p ON pr.product_id = p.id
        WHERE pr.id = :review_id
    ";

  $reviewStmt = $pdo->prepare($reviewSql);
  $reviewStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
  $reviewStmt->execute();

  $review = $reviewStmt->fetch(PDO::FETCH_ASSOC);

  if (!$review) {
    throw new Exception('Отзыв не найден');
  }

  // Генерируем HTML
  $html = generateReportsHtml($review, $reports);

  echo json_encode([
    'success' => true,
    'html' => $html
  ]);

} catch (Exception $e) {
  error_log("Get product review reports error: " . $e->getMessage());
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}

function generateReportsHtml($review, $reports)
{
  $starsHtml = '';
  for ($i = 1; $i <= 5; $i++) {
    $starClass = $i <= $review['rating'] ? 'star filled' : 'star';
    $starsHtml .= "<span class=\"{$starClass}\">★</span>";
  }

  $statusLabels = [
    'pending' => 'На рассмотрении',
    'reviewed' => 'Рассмотрено',
    'resolved' => 'Решено'
  ];

  $reportsHtml = '';
  if (empty($reports)) {
    $reportsHtml = '<div class="no-reports">Жалоб на этот отзыв нет</div>';
  } else {
    foreach ($reports as $report) {
      $avatar = $report['telegram_avatar'] ?? 'https://via.placeholder.com/40x40/6a7e9f/ffffff?text=' . substr($report['telegram_first_name'], 0, 1);
      $statusLabel = $statusLabels[$report['status']] ?? $report['status'];
      $statusClass = "status-{$report['status']}";

      $reportsHtml .= "
                <div class=\"report-item\">
                    <div class=\"report-header\">
                        <div class=\"report-user\">
                            <div class=\"user-avatar\">
                                <img src=\"{$avatar}\" alt=\"Avatar\" width=\"40\" height=\"40\">
                            </div>
                            <div class=\"user-info\">
                                <div class=\"user-name\">" . htmlspecialchars($report['telegram_first_name'] . ' ' . $report['telegram_last_name']) . "</div>
                                <div class=\"user-username\">@" . htmlspecialchars($report['telegram_username']) . "</div>
                            </div>
                        </div>
                        <div class=\"report-meta\">
                            <div class=\"report-date\">" . date('d.m.Y H:i', strtotime($report['created_at'])) . "</div>
                            <div class=\"report-status {$statusClass}\">{$statusLabel}</div>
                        </div>
                    </div>
                    " . (!empty($report['reason']) ? "
                        <div class=\"report-reason\">
                            <strong>Причина жалобы:</strong>
                            <p>" . nl2br(htmlspecialchars($report['reason'])) . "</p>
                        </div>
                    " : "") . "
                    " . (!empty($report['moderator_comment']) ? "
                        <div class=\"moderator-comment\">
                            <strong>Комментарий модератора:</strong>
                            <p>" . nl2br(htmlspecialchars($report['moderator_comment'])) . "</p>
                        </div>
                    " : "") . "
                </div>
            ";
    }
  }

  return "
        <div class=\"reports-details\">
            <div class=\"review-info\">
                <h6>Отзыв:</h6>
                <div class=\"review-summary\">
                    <div class=\"review-user\">
                        <span class=\"user-name\">" . htmlspecialchars($review['telegram_first_name'] . ' ' . $review['telegram_last_name']) . "</span>
                        <span class=\"user-username\">@" . htmlspecialchars($review['telegram_username']) . "</span>
                    </div>
                    <div class=\"review-rating\">
                        {$starsHtml}
                        <span class=\"rating-value\">{$review['rating']}/5</span>
                    </div>
                    <div class=\"review-product\">Товар: " . htmlspecialchars($review['product_title']) . "</div>
                    <div class=\"review-text\">" . htmlspecialchars(substr($review['text'], 0, 100)) . (strlen($review['text']) > 100 ? '...' : '') . "</div>
                </div>
            </div>

            <div class=\"reports-section\">
                <h6>Жалобы (" . count($reports) . "):</h6>
                <div class=\"reports-list\">
                    {$reportsHtml}
                </div>
            </div>

            <div class=\"reports-actions\">
                <button class=\"btn btn-primary btn-sm\" onclick=\"markReportsAsReviewed({$review['id']})\">
                    <i class=\"fas fa-check\"></i> Отметить как рассмотренные
                </button>
            </div>
        </div>

        <style>
        .reports-details {
            max-width: 100%;
        }
        
        .review-info {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .review-info h6,
        .reports-section h6 {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .review-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
        }
        
        .review-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
        }
        
        .user-username {
            color: #666;
            font-size: 14px;
        }
        
        .review-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .star {
            color: #ddd;
            font-size: 16px;
        }
        
        .star.filled {
            color: #ffc107;
        }
        
        .rating-value {
            margin-left: 5px;
            font-weight: 600;
        }
        
        .review-product {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .review-text {
            color: #333;
            line-height: 1.5;
        }
        
        .reports-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .report-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #dc3545;
        }
        
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        
        .report-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar img {
            border-radius: 50%;
            border: 2px solid #e9ecef;
        }
        
        .report-meta {
            text-align: right;
        }
        
        .report-date {
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .report-status {
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
        
        .status-reviewed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        
        .report-reason,
        .moderator-comment {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 6px;
        }
        
        .report-reason strong,
        .moderator-comment strong {
            color: #333;
            display: block;
            margin-bottom: 5px;
        }
        
        .report-reason p,
        .moderator-comment p {
            color: #666;
            margin: 0;
            line-height: 1.5;
        }
        
        .no-reports {
            text-align: center;
            color: #666;
            padding: 40px 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .reports-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            text-align: right;
        }
        </style>
    ";
}
?>
