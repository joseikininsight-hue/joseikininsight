<?php
/**
 * Template Part: Grant & Column Tabs Section - Yahoo! News Style
 * 補助金・コラム統合タブセクション - Yahoo!ニュース風デザイン
 * 
 * @package Grant_Insight_Perfect
 * @version 22.0.0 - Yahoo! News List Style Edition
 * 
 * Features:
 * - Yahoo! News inspired list design
 * - Clean and readable layout
 * - Unified tabs for grants, columns, and news
 * - Sidebar with rankings
 * - Mobile responsive
 * - SEO optimized
 * - No ad spaces
 */

if (!defined('ABSPATH')) exit;

// ===== データ取得 =====

// 補助金データ
$deadline_soon_grants = new WP_Query([
    'post_type' => 'grant',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'meta_key' => 'deadline_date',
    'orderby' => 'meta_value',
    'order' => 'ASC',
    'meta_query' => [
        [
            'key' => 'deadline_date',
            'value' => date('Y-m-d'),
            'compare' => '>=',
            'type' => 'DATE'
        ]
    ],
    'no_found_rows' => true,
]);

$new_grants = new WP_Query([
    'post_type' => 'grant',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
]);

$featured_grants = new WP_Query([
    'post_type' => 'grant',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'meta_key' => 'is_featured',
    'meta_value' => '1',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
]);

// コラムデータ
$new_columns = new WP_Query([
    'post_type' => 'column',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
]);

$popular_columns = new WP_Query([
    'post_type' => 'column',
    'posts_per_page' => 10,
    'post_status' => 'publish',
    'meta_key' => 'view_count',
    'orderby' => 'meta_value_num',
    'order' => 'DESC',
    'no_found_rows' => true,
]);

// お知らせデータ
$news = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 15,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC',
    'no_found_rows' => true,
]);

// ランキングデータ
$grant_ranking = function_exists('ji_get_ranking') ? ji_get_ranking('grant', 7, 10) : [];
$column_ranking = function_exists('ji_get_ranking') ? ji_get_ranking('column', 7, 10) : [];

// 統計情報
$total_grants = wp_count_posts('grant')->publish;
$total_columns = wp_count_posts('column')->publish;
$total_news = wp_count_posts('post')->publish;

// 構造化データ
$schema_data = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => '補助金・コラム・お知らせ総合情報',
    'description' => '最新の補助金情報、専門家コラム、サイトのお知らせを一箇所で確認できます。',
    'url' => home_url('/#tabs-section'),
    'inLanguage' => 'ja-JP',
];
?>

<!-- 構造化データ -->
<script type="application/ld+json">
<?php echo wp_json_encode($schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- ============================================
     Grant & Column Tabs Section - Yahoo! News Style
     ============================================ -->

<section class="gi-tabs-section-news" 
         id="tabs-section" 
         role="region" 
         aria-labelledby="tabs-section-title"
         itemscope 
         itemtype="https://schema.org/CollectionPage">
    
    <div class="gi-tabs-container">
        
        <!-- セクションヘッダー -->
        <header class="gi-tabs-header">
            <div class="gi-header-content">
                <h2 id="tabs-section-title" class="gi-section-title" itemprop="name">
                    <svg class="gi-title-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                    </svg>
                    <span>最新情報</span>
                </h2>
                <p class="gi-section-desc" itemprop="description">
                    補助金・コラム・お知らせの最新情報を一箇所でチェック
                </p>
            </div>
        </header>

        <!-- 2カラムレイアウト -->
        <div class="gi-tabs-layout">
            
            <!-- メインコンテンツ -->
            <div class="gi-tabs-main">
                
                <!-- タブナビゲーション -->
                <nav class="gi-tabs-nav" role="tablist" aria-label="情報カテゴリ">
                    <button class="gi-tab-btn active" 
                            role="tab" 
                            id="tab-grants" 
                            aria-selected="true" 
                            aria-controls="panel-grants" 
                            data-tab="grants">
                        <svg class="gi-tab-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        </svg>
                        <span class="gi-tab-label">補助金</span>
                        <span class="gi-tab-count"><?php echo number_format($total_grants); ?></span>
                    </button>
                    
                    <button class="gi-tab-btn" 
                            role="tab" 
                            id="tab-columns" 
                            aria-selected="false" 
                            aria-controls="panel-columns" 
                            data-tab="columns">
                        <svg class="gi-tab-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <span class="gi-tab-label">コラム</span>
                        <span class="gi-tab-count"><?php echo number_format($total_columns); ?></span>
                    </button>
                    
                    <button class="gi-tab-btn" 
                            role="tab" 
                            id="tab-news" 
                            aria-selected="false" 
                            aria-controls="panel-news" 
                            data-tab="news">
                        <svg class="gi-tab-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4"/>
                            <line x1="10" y1="1" x2="10" y2="4"/>
                            <line x1="14" y1="1" x2="14" y2="4"/>
                        </svg>
                        <span class="gi-tab-label">お知らせ</span>
                        <span class="gi-tab-count"><?php echo number_format($total_news); ?></span>
                    </button>
                </nav>

                <!-- タブコンテンツ -->
                <div class="gi-tabs-content">
                    
                    <!-- 補助金タブ -->
                    <div class="gi-tab-panel active" 
                         id="panel-grants" 
                         role="tabpanel" 
                         aria-labelledby="tab-grants">
                        
                        <!-- サブタブ -->
                        <div class="gi-subtabs-nav">
                            <button class="gi-subtab-btn active" data-subtab="grant-featured">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                注目
                            </button>
                            <button class="gi-subtab-btn" data-subtab="grant-deadline">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                                締切間近
                            </button>
                            <button class="gi-subtab-btn" data-subtab="grant-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                </svg>
                                新着
                            </button>
                        </div>

                        <!-- 注目の補助金 -->
                        <div class="gi-subtab-content active" data-content="grant-featured">
                            <?php if ($featured_grants->have_posts()) : ?>
                                <div class="gi-news-list">
                                    <?php while ($featured_grants->have_posts()) : $featured_grants->the_post(); 
                                        $deadline = get_post_meta(get_the_ID(), 'deadline_date', true);
                                        $amount = get_post_meta(get_the_ID(), 'grant_amount_max', true);
                                        $categories = get_the_terms(get_the_ID(), 'grant_category');
                                        $prefecture = get_the_terms(get_the_ID(), 'grant_prefecture');
                                        
                                        $days_left = null;
                                        if ($deadline) {
                                            $deadline_date = new DateTime($deadline);
                                            $now = new DateTime();
                                            $diff = $now->diff($deadline_date);
                                            $days_left = $diff->days;
                                        }
                                    ?>
                                        <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                            <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                                <div class="gi-news-header">
                                                    <div class="gi-news-badges">
                                                        <span class="gi-badge gi-badge-featured">注目</span>
                                                        <?php if ($days_left !== null && $days_left <= 14) : ?>
                                                            <span class="gi-badge gi-badge-urgent">あと<?php echo $days_left; ?>日</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                        <?php echo get_the_date('Y/m/d'); ?>
                                                    </time>
                                                </div>
                                                
                                                <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                                
                                                <div class="gi-news-meta">
                                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                                        <span class="gi-meta-tag gi-meta-category">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                                            </svg>
                                                            <?php echo esc_html($categories[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($prefecture && !is_wp_error($prefecture)) : ?>
                                                        <span class="gi-meta-tag gi-meta-location">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                                <circle cx="12" cy="10" r="3"/>
                                                            </svg>
                                                            <?php echo esc_html($prefecture[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($amount) : ?>
                                                        <span class="gi-meta-tag gi-meta-amount">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <line x1="12" y1="1" x2="12" y2="23"/>
                                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                                            </svg>
                                                            最大<?php echo number_format($amount); ?>万円
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="gi-empty-state">
                                    <p>注目の補助金はありません</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="gi-panel-footer">
                                <a href="<?php echo esc_url(home_url('/grants/?filter=featured')); ?>" class="gi-more-link">
                                    注目の補助金をもっと見る
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- 締切間近の補助金 -->
                        <div class="gi-subtab-content" data-content="grant-deadline">
                            <?php if ($deadline_soon_grants->have_posts()) : ?>
                                <div class="gi-news-list">
                                    <?php while ($deadline_soon_grants->have_posts()) : $deadline_soon_grants->the_post(); 
                                        $deadline = get_post_meta(get_the_ID(), 'deadline_date', true);
                                        $amount = get_post_meta(get_the_ID(), 'grant_amount_max', true);
                                        $categories = get_the_terms(get_the_ID(), 'grant_category');
                                        $prefecture = get_the_terms(get_the_ID(), 'grant_prefecture');
                                        
                                        $days_left = null;
                                        if ($deadline) {
                                            $deadline_date = new DateTime($deadline);
                                            $now = new DateTime();
                                            $diff = $now->diff($deadline_date);
                                            $days_left = $diff->days;
                                        }
                                    ?>
                                        <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                            <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                                <div class="gi-news-header">
                                                    <div class="gi-news-badges">
                                                        <span class="gi-badge gi-badge-urgent">あと<?php echo $days_left; ?>日</span>
                                                    </div>
                                                    <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                        <?php echo get_the_date('Y/m/d'); ?>
                                                    </time>
                                                </div>
                                                
                                                <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                                
                                                <div class="gi-news-meta">
                                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                                        <span class="gi-meta-tag gi-meta-category">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                                            </svg>
                                                            <?php echo esc_html($categories[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($prefecture && !is_wp_error($prefecture)) : ?>
                                                        <span class="gi-meta-tag gi-meta-location">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                                <circle cx="12" cy="10" r="3"/>
                                                            </svg>
                                                            <?php echo esc_html($prefecture[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($amount) : ?>
                                                        <span class="gi-meta-tag gi-meta-amount">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <line x1="12" y1="1" x2="12" y2="23"/>
                                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                                            </svg>
                                                            最大<?php echo number_format($amount); ?>万円
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="gi-empty-state">
                                    <p>締切間近の補助金はありません</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="gi-panel-footer">
                                <a href="<?php echo esc_url(home_url('/grants/?orderby=deadline')); ?>" class="gi-more-link">
                                    締切間近の補助金をもっと見る
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- 新着の補助金 -->
                        <div class="gi-subtab-content" data-content="grant-new">
                            <?php if ($new_grants->have_posts()) : ?>
                                <div class="gi-news-list">
                                    <?php while ($new_grants->have_posts()) : $new_grants->the_post(); 
                                        $deadline = get_post_meta(get_the_ID(), 'deadline_date', true);
                                        $amount = get_post_meta(get_the_ID(), 'grant_amount_max', true);
                                        $categories = get_the_terms(get_the_ID(), 'grant_category');
                                        $prefecture = get_the_terms(get_the_ID(), 'grant_prefecture');
                                    ?>
                                        <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                            <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                                <div class="gi-news-header">
                                                    <div class="gi-news-badges">
                                                        <span class="gi-badge gi-badge-new">NEW</span>
                                                    </div>
                                                    <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                        <?php echo get_the_date('Y/m/d'); ?>
                                                    </time>
                                                </div>
                                                
                                                <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                                
                                                <div class="gi-news-meta">
                                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                                        <span class="gi-meta-tag gi-meta-category">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                                            </svg>
                                                            <?php echo esc_html($categories[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($prefecture && !is_wp_error($prefecture)) : ?>
                                                        <span class="gi-meta-tag gi-meta-location">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                                                <circle cx="12" cy="10" r="3"/>
                                                            </svg>
                                                            <?php echo esc_html($prefecture[0]->name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($amount) : ?>
                                                        <span class="gi-meta-tag gi-meta-amount">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <line x1="12" y1="1" x2="12" y2="23"/>
                                                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                                            </svg>
                                                            最大<?php echo number_format($amount); ?>万円
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </a>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="gi-empty-state">
                                    <p>新着の補助金はありません</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="gi-panel-footer">
                                <a href="<?php echo esc_url(home_url('/grants/?orderby=new')); ?>" class="gi-more-link">
                                    新着補助金をもっと見る
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- コラムタブ -->
                    <div class="gi-tab-panel" 
                         id="panel-columns" 
                         role="tabpanel" 
                         aria-labelledby="tab-columns">
                        
                        <!-- サブタブ -->
                        <div class="gi-subtabs-nav">
                            <button class="gi-subtab-btn active" data-subtab="column-new">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                </svg>
                                新着
                            </button>
                            <button class="gi-subtab-btn" data-subtab="column-popular">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                                </svg>
                                人気
                            </button>
                        </div>

                        <!-- 新着コラム -->
                        <div class="gi-subtab-content active" data-content="column-new">
                            <?php if ($new_columns->have_posts()) : ?>
                                <div class="gi-news-list">
                                    <?php while ($new_columns->have_posts()) : $new_columns->the_post(); 
                                        $categories = get_the_terms(get_the_ID(), 'column_category');
                                        $view_count = get_post_meta(get_the_ID(), 'view_count', true);
                                    ?>
                                        <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                            <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                                <div class="gi-news-header">
                                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                                        <span class="gi-news-category"><?php echo esc_html($categories[0]->name); ?></span>
                                                    <?php endif; ?>
                                                    <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                        <?php echo get_the_date('Y/m/d'); ?>
                                                    </time>
                                                </div>
                                                
                                                <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                                
                                                <?php if ($view_count) : ?>
                                                    <div class="gi-news-meta">
                                                        <span class="gi-meta-views">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                <circle cx="12" cy="12" r="3"/>
                                                            </svg>
                                                            <?php echo number_format($view_count); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="gi-empty-state">
                                    <p>新着のコラムはありません</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="gi-panel-footer">
                                <a href="<?php echo esc_url(home_url('/columns/')); ?>" class="gi-more-link">
                                    コラムをもっと見る
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- 人気コラム -->
                        <div class="gi-subtab-content" data-content="column-popular">
                            <?php if ($popular_columns->have_posts()) : ?>
                                <div class="gi-news-list">
                                    <?php while ($popular_columns->have_posts()) : $popular_columns->the_post(); 
                                        $categories = get_the_terms(get_the_ID(), 'column_category');
                                        $view_count = get_post_meta(get_the_ID(), 'view_count', true);
                                    ?>
                                        <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                            <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                                <div class="gi-news-header">
                                                    <?php if ($categories && !is_wp_error($categories)) : ?>
                                                        <span class="gi-news-category"><?php echo esc_html($categories[0]->name); ?></span>
                                                    <?php endif; ?>
                                                    <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                        <?php echo get_the_date('Y/m/d'); ?>
                                                    </time>
                                                </div>
                                                
                                                <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                                
                                                <?php if ($view_count) : ?>
                                                    <div class="gi-news-meta">
                                                        <span class="gi-meta-views">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                                <circle cx="12" cy="12" r="3"/>
                                                            </svg>
                                                            <?php echo number_format($view_count); ?>
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        </article>
                                    <?php endwhile; wp_reset_postdata(); ?>
                                </div>
                            <?php else : ?>
                                <div class="gi-empty-state">
                                    <p>人気のコラムはありません</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="gi-panel-footer">
                                <a href="<?php echo esc_url(home_url('/columns/?orderby=popular')); ?>" class="gi-more-link">
                                    人気コラムをもっと見る
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- お知らせタブ -->
                    <div class="gi-tab-panel" 
                         id="panel-news" 
                         role="tabpanel" 
                         aria-labelledby="tab-news">
                        
                        <?php if ($news->have_posts()) : ?>
                            <div class="gi-news-list gi-news-simple">
                                <?php while ($news->have_posts()) : $news->the_post(); 
                                    $categories = get_the_category();
                                ?>
                                    <article class="gi-news-item" itemscope itemtype="https://schema.org/Article">
                                        <a href="<?php the_permalink(); ?>" class="gi-news-link" itemprop="url">
                                            <div class="gi-news-header">
                                                <?php if ($categories) : ?>
                                                    <span class="gi-news-category"><?php echo esc_html($categories[0]->name); ?></span>
                                                <?php endif; ?>
                                                <time class="gi-news-date" datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                                                    <?php echo get_the_date('Y/m/d'); ?>
                                                </time>
                                            </div>
                                            <h3 class="gi-news-title" itemprop="headline"><?php the_title(); ?></h3>
                                        </a>
                                    </article>
                                <?php endwhile; wp_reset_postdata(); ?>
                            </div>
                        <?php else : ?>
                            <div class="gi-empty-state">
                                <p>お知らせはありません</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="gi-panel-footer">
                            <a href="<?php echo esc_url(home_url('/news/')); ?>" class="gi-more-link">
                                お知らせをもっと見る
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                    <polyline points="12 5 19 12 12 19"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- サイドバー -->
            <aside class="gi-tabs-sidebar" role="complementary" aria-label="ランキング">
                
                <!-- 補助金ランキング -->
                <section class="gi-sidebar-widget gi-ranking-widget">
                    <h3 class="gi-widget-title">
                        <svg class="gi-widget-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        補助金ランキング
                    </h3>
                    <div class="gi-widget-content">
                        <?php if (!empty($grant_ranking)) : ?>
                            <ol class="gi-ranking-list">
                                <?php foreach (array_slice($grant_ranking, 0, 10) as $index => $item) : ?>
                                    <li class="gi-ranking-item rank-<?php echo $index + 1; ?>">
                                        <a href="<?php echo get_permalink($item->post_id); ?>" class="gi-ranking-link">
                                            <span class="gi-rank-number"><?php echo $index + 1; ?></span>
                                            <span class="gi-rank-title"><?php echo esc_html(get_the_title($item->post_id)); ?></span>
                                            <span class="gi-rank-views">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                <?php echo number_format($item->total_views); ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php else : ?>
                            <p class="gi-no-data">データがありません</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- コラムランキング -->
                <section class="gi-sidebar-widget gi-ranking-widget">
                    <h3 class="gi-widget-title">
                        <svg class="gi-widget-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        コラムランキング
                    </h3>
                    <div class="gi-widget-content">
                        <?php if (!empty($column_ranking)) : ?>
                            <ol class="gi-ranking-list">
                                <?php foreach (array_slice($column_ranking, 0, 10) as $index => $item) : ?>
                                    <li class="gi-ranking-item rank-<?php echo $index + 1; ?>">
                                        <a href="<?php echo get_permalink($item->post_id); ?>" class="gi-ranking-link">
                                            <span class="gi-rank-number"><?php echo $index + 1; ?></span>
                                            <span class="gi-rank-title"><?php echo esc_html(get_the_title($item->post_id)); ?></span>
                                            <span class="gi-rank-views">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                                <?php echo number_format($item->total_views); ?>
                                            </span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php else : ?>
                            <p class="gi-no-data">データがありません</p>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</section>

<style>
/* ===================================
   Grant & Column Tabs Section - Yahoo! News Style
   ニュース形式のシンプルなリストデザイン
   =================================== */

:root {
    --gi-yahoo-primary: #000000;
    --gi-yahoo-secondary: #ffffff;
    --gi-yahoo-gray-50: #fafafa;
    --gi-yahoo-gray-100: #f5f5f5;
    --gi-yahoo-gray-200: #e5e5e5;
    --gi-yahoo-gray-300: #d4d4d4;
    --gi-yahoo-gray-400: #a3a3a3;
    --gi-yahoo-gray-500: #737373;
    --gi-yahoo-gray-600: #525252;
    --gi-yahoo-gray-700: #404040;
    --gi-yahoo-gray-800: #262626;
    --gi-yahoo-gray-900: #171717;
    --gi-yahoo-red: #ff0033;
    --gi-yahoo-blue: #0078ff;
    --gi-yahoo-green: #00cc00;
    --gi-yahoo-orange: #ff6600;
    --gi-yahoo-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    --gi-yahoo-radius: 4px;
    --gi-yahoo-font: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Noto Sans JP', sans-serif;
}

/* ===== Section Base ===== */
.gi-tabs-section-news {
    padding: 60px 0;
    background: var(--gi-yahoo-gray-50);
    font-family: var(--gi-yahoo-font);
}

.gi-tabs-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 16px;
}

/* ===== Header ===== */
.gi-tabs-header {
    margin-bottom: 32px;
}

.gi-header-content {
    text-align: center;
}

.gi-section-title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    font-size: 32px;
    font-weight: 900;
    color: var(--gi-yahoo-primary);
    margin: 0 0 12px 0;
    letter-spacing: -0.02em;
}

.gi-title-icon {
    flex-shrink: 0;
}

.gi-section-desc {
    font-size: 15px;
    color: var(--gi-yahoo-gray-600);
    margin: 0;
    font-weight: 500;
}

/* ===== Layout ===== */
.gi-tabs-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 24px;
    align-items: start;
}

.gi-tabs-main {
    min-width: 0;
}

.gi-tabs-sidebar {
    position: sticky;
    top: 24px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* ===== Tabs Navigation ===== */
.gi-tabs-nav {
    display: flex;
    gap: 2px;
    background: var(--gi-yahoo-gray-200);
    padding: 2px;
    margin-bottom: 0;
}

.gi-tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 20px;
    background: var(--gi-yahoo-secondary);
    border: none;
    color: var(--gi-yahoo-gray-700);
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.gi-tab-btn:hover {
    background: var(--gi-yahoo-gray-100);
    color: var(--gi-yahoo-primary);
}

.gi-tab-btn.active {
    background: var(--gi-yahoo-primary);
    color: var(--gi-yahoo-secondary);
}

.gi-tab-icon {
    flex-shrink: 0;
}

.gi-tab-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 20px;
    padding: 0 6px;
    font-size: 11px;
    font-weight: 800;
    background: var(--gi-yahoo-gray-300);
    color: var(--gi-yahoo-gray-700);
}

.gi-tab-btn.active .gi-tab-count {
    background: var(--gi-yahoo-secondary);
    color: var(--gi-yahoo-primary);
}

/* ===== Tabs Content ===== */
.gi-tabs-content {
    background: var(--gi-yahoo-secondary);
    border: 1px solid var(--gi-yahoo-gray-200);
    box-shadow: var(--gi-yahoo-shadow);
}

.gi-tab-panel {
    display: none;
    padding: 0;
}

.gi-tab-panel.active {
    display: block;
}

/* ===== Subtabs ===== */
.gi-subtabs-nav {
    display: flex;
    gap: 2px;
    padding: 16px 16px 0;
    border-bottom: 2px solid var(--gi-yahoo-gray-200);
}

.gi-subtab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: transparent;
    border: none;
    color: var(--gi-yahoo-gray-600);
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
}

.gi-subtab-btn:hover {
    color: var(--gi-yahoo-primary);
    background: var(--gi-yahoo-gray-50);
}

.gi-subtab-btn.active {
    color: var(--gi-yahoo-primary);
}

.gi-subtab-btn.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--gi-yahoo-primary);
}

.gi-subtab-content {
    display: none;
}

.gi-subtab-content.active {
    display: block;
}

/* ===== News List ===== */
.gi-news-list {
    padding: 0;
}

.gi-news-item {
    border-bottom: 1px solid var(--gi-yahoo-gray-200);
    transition: background 0.2s;
}

.gi-news-item:hover {
    background: var(--gi-yahoo-gray-50);
}

.gi-news-item:last-child {
    border-bottom: none;
}

.gi-news-link {
    display: block;
    padding: 16px 20px;
    text-decoration: none;
    color: inherit;
}

.gi-news-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 8px;
}

.gi-news-badges {
    display: flex;
    align-items: center;
    gap: 6px;
}

.gi-badge {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--gi-yahoo-secondary);
}

.gi-badge-featured {
    background: var(--gi-yahoo-orange);
}

.gi-badge-urgent {
    background: var(--gi-yahoo-red);
}

.gi-badge-new {
    background: var(--gi-yahoo-green);
}

.gi-news-category {
    display: inline-block;
    padding: 2px 8px;
    background: var(--gi-yahoo-gray-100);
    color: var(--gi-yahoo-gray-700);
    font-size: 11px;
    font-weight: 700;
}

.gi-news-date {
    font-size: 12px;
    color: var(--gi-yahoo-gray-500);
    font-weight: 600;
}

.gi-news-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--gi-yahoo-primary);
    margin: 0 0 8px 0;
    line-height: 1.6;
}

.gi-news-link:hover .gi-news-title {
    color: var(--gi-yahoo-blue);
}

.gi-news-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.gi-meta-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--gi-yahoo-gray-600);
    font-weight: 600;
}

.gi-meta-amount {
    color: var(--gi-yahoo-blue);
    font-weight: 700;
}

.gi-meta-views {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--gi-yahoo-gray-500);
    font-weight: 600;
}

/* ===== Panel Footer ===== */
.gi-panel-footer {
    padding: 16px;
    border-top: 1px solid var(--gi-yahoo-gray-200);
    text-align: center;
    background: var(--gi-yahoo-gray-50);
}

.gi-more-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--gi-yahoo-primary);
    color: var(--gi-yahoo-secondary);
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
}

.gi-more-link:hover {
    background: var(--gi-yahoo-gray-800);
    transform: translateY(-2px);
    box-shadow: var(--gi-yahoo-shadow);
}

/* ===== Empty State ===== */
.gi-empty-state {
    padding: 40px 20px;
    text-align: center;
    color: var(--gi-yahoo-gray-500);
}

.gi-empty-state p {
    margin: 0;
    font-size: 14px;
}

/* ===== Sidebar ===== */
.gi-sidebar-widget {
    background: var(--gi-yahoo-secondary);
    border: 1px solid var(--gi-yahoo-gray-200);
    box-shadow: var(--gi-yahoo-shadow);
    overflow: hidden;
}

.gi-widget-title {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: var(--gi-yahoo-gray-50);
    border-bottom: 2px solid var(--gi-yahoo-primary);
    font-size: 14px;
    font-weight: 700;
    color: var(--gi-yahoo-primary);
    margin: 0;
}

.gi-widget-icon {
    flex-shrink: 0;
}

.gi-widget-content {
    padding: 16px;
}

.gi-no-data {
    text-align: center;
    padding: 20px;
    color: var(--gi-yahoo-gray-500);
    font-size: 13px;
    margin: 0;
}

/* ===== Ranking ===== */
.gi-ranking-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.gi-ranking-item {
    border-bottom: 1px solid var(--gi-yahoo-gray-200);
    padding-bottom: 8px;
}

.gi-ranking-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.gi-ranking-link {
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: inherit;
}

.gi-rank-number {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 800;
    background: var(--gi-yahoo-gray-200);
    color: var(--gi-yahoo-gray-700);
}

.rank-1 .gi-rank-number {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: var(--gi-yahoo-secondary);
}

.rank-2 .gi-rank-number {
    background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
    color: var(--gi-yahoo-secondary);
}

.rank-3 .gi-rank-number {
    background: linear-gradient(135deg, #CD7F32, #A0522D);
    color: var(--gi-yahoo-secondary);
}

.gi-rank-title {
    flex: 1;
    font-size: 13px;
    font-weight: 600;
    color: var(--gi-yahoo-primary);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.gi-ranking-link:hover .gi-rank-title {
    color: var(--gi-yahoo-blue);
    text-decoration: underline;
}

.gi-rank-views {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--gi-yahoo-gray-500);
}

/* ===== Responsive ===== */
@media (max-width: 1024px) {
    .gi-tabs-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .gi-tabs-sidebar {
        display: none;
    }
}

@media (max-width: 768px) {
    .gi-tabs-section-news {
        padding: 40px 0;
    }
    
    .gi-section-title {
        font-size: 24px;
    }
    
    .gi-tabs-nav {
        flex-direction: column;
    }
    
    .gi-tab-btn {
        justify-content: flex-start;
    }
    
    .gi-subtabs-nav {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .gi-subtab-btn {
        white-space: nowrap;
    }
    
    .gi-news-link {
        padding: 12px 16px;
    }
    
    .gi-news-title {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .gi-tabs-container {
        padding: 0 12px;
    }
    
    .gi-section-title {
        font-size: 20px;
    }
    
    .gi-tab-label {
        display: none;
    }
    
    .gi-tab-btn.active .gi-tab-label {
        display: inline;
    }
}

/* ===== Print ===== */
@media print {
    .gi-tabs-nav,
    .gi-subtabs-nav,
    .gi-panel-footer,
    .gi-tabs-sidebar {
        display: none !important;
    }
    
    .gi-tab-panel {
        display: block !important;
    }
}
</style>

<script>
/**
 * Grant & Column Tabs Section - JavaScript
 * タブ切り替え機能
 */
(function() {
    'use strict';
    
    // メインタブ
    const mainTabs = document.querySelectorAll('.gi-tab-btn');
    const mainPanels = document.querySelectorAll('.gi-tab-panel');
    
    mainTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // タブのアクティブ切り替え
            mainTabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            
            // パネルの表示切り替え
            mainPanels.forEach(p => {
                p.classList.remove('active');
            });
            document.getElementById(`panel-${targetTab}`).classList.add('active');
        });
    });
    
    // サブタブ
    const subtabs = document.querySelectorAll('.gi-subtab-btn');
    
    subtabs.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetSubtab = this.dataset.subtab;
            const panel = this.closest('.gi-tab-panel');
            
            // サブタブのアクティブ切り替え
            panel.querySelectorAll('.gi-subtab-btn').forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            
            // サブコンテンツの表示切り替え
            panel.querySelectorAll('.gi-subtab-content').forEach(c => {
                c.classList.remove('active');
            });
            panel.querySelector(`[data-content="${targetSubtab}"]`).classList.add('active');
        });
    });
    
    console.log('✅ Grant & Column Tabs Section v22.0 (News Style) Initialized');
})();
</script>