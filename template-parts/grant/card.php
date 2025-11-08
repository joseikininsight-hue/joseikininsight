<?php
/**
 * Template Part: Grant Card - Yahoo! News Compact Style
 * 補助金カード表示用テンプレート（Yahoo!ニュース風コンパクトカード）
 * 
 * @package Grant_Insight_Perfect
 * @version 3.0.0 - Yahoo! News Compact Style
 * 
 * Required variables:
 * @var WP_Post $grant - Grant post object
 * @var int $position - Position in list (for structured data)
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit;
}

// query_varから$grantを取得
$grant = get_query_var('grant');

// 必須変数チェック
if (!$grant || !($grant instanceof WP_Post)) {
    return;
}

// メタデータ取得（ACFフィールド）
$deadline = get_field('deadline', $grant->ID);
$deadline_date = get_field('deadline_date', $grant->ID);
$max_amount = get_field('max_amount', $grant->ID);
$organization = get_field('organization', $grant->ID);
$is_featured = get_field('is_featured', $grant->ID);
$view_count = get_post_meta($grant->ID, 'view_count', true);

// 新規投稿判定（7日以内）
$is_new = (strtotime($grant->post_date) > strtotime('-7 days'));

// 締切日の処理
$deadline_display = '';
$deadline_iso = '';
$is_deadline_soon = false;

if ($deadline_date) {
    $timestamp = strtotime($deadline_date);
    if ($timestamp) {
        $deadline_display = date('Y/m/d', $timestamp);
        $deadline_iso = date('Y-m-d', $timestamp);
        
        // 30日以内の締切判定
        $days_until_deadline = floor(($timestamp - time()) / (60 * 60 * 24));
        $is_deadline_soon = ($days_until_deadline >= 0 && $days_until_deadline <= 30);
    }
} elseif ($deadline) {
    $deadline_display = $deadline;
}

// パーマリンク
$permalink = get_permalink($grant->ID);

// カテゴリー取得
$grant_categories = get_the_terms($grant->ID, 'grant_category');
$main_category = null;
if ($grant_categories && !is_wp_error($grant_categories)) {
    $main_category = $grant_categories[0];
}

// 都道府県取得
$grant_prefectures = get_the_terms($grant->ID, 'grant_prefecture');
$prefecture_display = '全国';
if ($grant_prefectures && !is_wp_error($grant_prefectures)) {
    $pref_count = count($grant_prefectures);
    if ($pref_count == 1) {
        $prefecture_display = $grant_prefectures[0]->name;
    } elseif ($pref_count <= 3) {
        $prefecture_display = implode('・', array_map(function($p) { return $p->name; }, $grant_prefectures));
    } elseif ($pref_count < 47) {
        $prefecture_display = $pref_count . '都道府県';
    }
}

// アイキャッチ画像
$thumbnail_id = get_post_thumbnail_id($grant->ID);
$thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'thumbnail') : '';

// 抜粋
$excerpt = get_the_excerpt($grant->ID);
if (empty($excerpt)) {
    $content = get_the_content(null, false, $grant->ID);
    $excerpt = wp_trim_words(strip_tags($content), 20, '...');
}

// Position for structured data
$position = isset($position) ? $position : 1;
?>

<article class="grant-card-compact" 
         role="listitem"
         itemscope 
         itemprop="itemListElement"
         itemtype="https://schema.org/Article">
    <meta itemprop="position" content="<?php echo $position; ?>">
    
    <a href="<?php echo esc_url($permalink); ?>" 
       class="card-link-compact"
       itemprop="url"
       aria-label="<?php echo esc_attr($grant->post_title); ?>の詳細を見る">
        
        <div class="card-inner">
            <!-- 左側：サムネイル -->
            <?php if ($thumbnail_url) : ?>
            <div class="card-thumb">
                <img src="<?php echo esc_url($thumbnail_url); ?>" 
                     alt="<?php echo esc_attr($grant->post_title); ?>"
                     itemprop="image"
                     loading="lazy">
                
                <!-- バッジ -->
                <?php if ($is_featured == '1') : ?>
                <span class="badge badge-featured">注目</span>
                <?php elseif ($is_new) : ?>
                <span class="badge badge-new">NEW</span>
                <?php elseif ($is_deadline_soon) : ?>
                <span class="badge badge-deadline">締切間近</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- 右側：テキスト情報 -->
            <div class="card-text">
                <!-- カテゴリ・地域 -->
                <div class="card-meta">
                    <?php if ($main_category) : ?>
                    <span class="meta-category"><?php echo esc_html($main_category->name); ?></span>
                    <?php endif; ?>
                    <span class="meta-location"><?php echo esc_html($prefecture_display); ?></span>
                    <?php if ($deadline_display) : ?>
                    <span class="meta-deadline <?php echo $is_deadline_soon ? 'urgent' : ''; ?>">
                        締切: <?php echo esc_html($deadline_display); ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <!-- タイトル -->
                <h3 class="card-title-compact" itemprop="headline">
                    <?php echo esc_html($grant->post_title); ?>
                </h3>
                
                <!-- 抜粋 -->
                <p class="card-excerpt-compact" itemprop="description">
                    <?php echo esc_html($excerpt); ?>
                </p>
                
                <!-- 下部メタ情報 -->
                <div class="card-footer-meta">
                    <?php if ($organization) : ?>
                    <span class="meta-org">
                        <i class="fas fa-building"></i>
                        <?php echo esc_html($organization); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($max_amount) : ?>
                    <span class="meta-amount">
                        <i class="fas fa-yen-sign"></i>
                        最大<?php echo esc_html($max_amount); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if ($view_count && $view_count > 0) : ?>
                    <span class="meta-views">
                        <i class="fas fa-eye"></i>
                        <?php echo number_format($view_count); ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </a>
</article>

<style>
/* ========================================
   Yahoo! News Compact Style Grant Card
   Yahoo!ニュース風コンパクトカード
   ======================================== */

.grant-card-compact {
    background: #ffffff;
    border-bottom: 1px solid #e5e5e5;
    transition: background-color 0.2s ease;
}

.grant-card-compact:hover {
    background-color: #f8f8f8;
}

.card-link-compact {
    text-decoration: none;
    color: inherit;
    display: block;
    padding: 16px;
}

.card-inner {
    display: flex;
    gap: 16px;
    align-items: flex-start;
}

/* 左側：サムネイル（Yahoo!風小型） */
.card-thumb {
    position: relative;
    flex-shrink: 0;
    width: 120px;
    height: 80px;
    border-radius: 4px;
    overflow: hidden;
    background: #f5f5f5;
}

.card-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.grant-card-compact:hover .card-thumb img {
    transform: scale(1.1);
}

/* バッジ（小型） */
.card-thumb .badge {
    position: absolute;
    top: 4px;
    left: 4px;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 700;
    border-radius: 2px;
    line-height: 1;
}

.badge-featured {
    background: #ffeb3b;
    color: #000000;
}

.badge-new {
    background: #2196f3;
    color: #ffffff;
}

.badge-deadline {
    background: #f44336;
    color: #ffffff;
    animation: pulse-small 2s ease-in-out infinite;
}

@keyframes pulse-small {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* 右側：テキスト */
.card-text {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* メタ情報（上部） */
.card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
}

.meta-category {
    padding: 3px 8px;
    background: #000000;
    color: #ffeb3b;
    border-radius: 2px;
    font-weight: 600;
}

.meta-location {
    padding: 3px 8px;
    background: #f5f5f5;
    color: #666666;
    border-radius: 2px;
    font-weight: 500;
}

.meta-deadline {
    padding: 3px 8px;
    background: #fff3e0;
    color: #ff9800;
    border-radius: 2px;
    font-weight: 600;
}

.meta-deadline.urgent {
    background: #ffebee;
    color: #f44336;
    font-weight: 700;
}

/* タイトル（Yahoo!風） */
.card-title-compact {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.4;
    color: #000000;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.2s ease;
}

.grant-card-compact:hover .card-title-compact {
    color: #0066cc;
}

/* 抜粋 */
.card-excerpt-compact {
    font-size: 13px;
    line-height: 1.5;
    color: #666666;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 下部メタ情報 */
.card-footer-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 12px;
    color: #999999;
}

.card-footer-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.card-footer-meta i {
    font-size: 11px;
}

.meta-org i {
    color: #2196f3;
}

.meta-amount i {
    color: #4caf50;
}

.meta-views i {
    color: #9c27b0;
}

/* レスポンシブ */
@media (max-width: 640px) {
    .card-inner {
        gap: 12px;
    }
    
    .card-thumb {
        width: 100px;
        height: 67px;
    }
    
    .card-title-compact {
        font-size: 15px;
    }
    
    .card-excerpt-compact {
        font-size: 12px;
    }
    
    .card-footer-meta {
        font-size: 11px;
        gap: 8px;
    }
}

/* グリッドなしの縦並びレイアウト */
.grants-grid {
    display: flex !important;
    flex-direction: column !important;
    gap: 0 !important;
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    overflow: hidden;
}

.grant-card-compact:last-child {
    border-bottom: none;
}
</style>
