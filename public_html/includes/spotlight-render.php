<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Models/Spotlight.php';

/**
 * Render a single spotlight unit as neutral DOM
 */
function render_spotlight_unit(array $s)
{
  $classes = ['unit', 'unit--showcase'];
  if (!empty($s['bg_style'])) {
    $classes[] = preg_replace('/[^a-z0-9\-_:]/i', '', $s['bg_style']);
  }

  $html = '<div class="' . implode(' ', $classes) . '" data-unit-id="' . (int) $s['id'] . '">';

  // media
  if (!empty($s['media_type']) && $s['media_type'] !== 'none' && !empty($s['media_url'])) {
    $html .= '<div class="unit__media">';
    if ($s['media_type'] === 'image') {
      $html .= '<img src="' . htmlspecialchars($s['media_url']) . '" alt="" loading="lazy" />';
    } elseif ($s['media_type'] === 'video') {
      $html .= '<video class="unit__video" autoplay muted playsinline loop preload="none">'
        . '<source src="' . htmlspecialchars($s['media_url']) . '" type="video/mp4" />'
        . '</video>';
    }
    $html .= '</div>';
  }

  $html .= '<div class="unit__body">';
  if (!empty($s['title'])) {
    $html .= '<h3 class="unit__title">' . htmlspecialchars($s['title']) . '</h3>';
  }
  if (!empty($s['body'])) {
    $html .= '<div class="unit__text">' . nl2br(htmlspecialchars($s['body'])) . '</div>';
  }
  if (!empty($s['cta_label']) && !empty($s['cta_url'])) {
    $url = htmlspecialchars($s['cta_url']);
    $label = htmlspecialchars($s['cta_label']);
    $html .= '<button type="button" class="unit__cta" onclick="window.location.href=\'' . $url . '\'">' . $label . '</button>';
  }
  $html .= '</div>';

  $html .= '</div>';
  return $html;
}

/**
 * Render a group of spotlights as a simple carousel (no ad-like naming)
 */
function render_spotlight_carousel(array $items)
{
  if (empty($items))
    return '';
  $html = '<div class="unit-rail" role="region">';
  $html .= '<div class="unit-rail__track">';
  foreach ($items as $s) {
    $html .= '<div class="unit-rail__slide">' . render_spotlight_unit($s) . '</div>';
  }
  $html .= '</div>';
  $html .= '</div>';
  return $html;
}

/**
 * Helper to fetch active spotlights for context and render either single or grouped
 */
function get_spotlights_html(string $context, ?string $groupKey = null)
{
  try {
    $model = new Spotlight();
    if ($groupKey) {
      $items = $model->getAll([
        'is_active' => 1,
        'only_current' => 1,
        'context' => $context,
        'group_key' => $groupKey,
      ]);
      return render_spotlight_carousel($items);
    }

    $items = $model->getActiveByContext($context);
    if (count($items) > 1) {
      return render_spotlight_carousel($items);
    } elseif (count($items) === 1) {
      return render_spotlight_unit($items[0]);
    }
    return '';
  } catch (Throwable $e) {
    error_log('Spotlights render error: ' . $e->getMessage());
    return '';
  }
}


