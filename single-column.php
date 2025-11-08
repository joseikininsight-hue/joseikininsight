<?php
/**
 * Single Column Template - Enhanced SEO & Wide Sidebar
 * コラム記事詳細ページ - SEO最適化 + ワイドサイドバー
 * 
 * 関連補助金連携:
 * - ACFの「related_grants」フィールドで手動設定された補助金を優先表示
 * - 設定がない場合、コラムカテゴリに基づいて自動的に関連補助金を表示
 * - サイドバー（PC）とメインコンテンツ下部（モバイル）の両方に表示
 * 
 * UX改善（v5.0.0）:
 * - 記事直後に最強CTA（AI診断・補助金検索）を設置 - コンバージョン最適化
 * - 「この記事はこんな方におすすめ」を記事冒頭に移動 - 結論ファースト
 * - サイドバー順序: 目次→関連補助金→人気記事→AIチャット（UX優先）
 * 
 * @package Grant_Insight_Perfect
 * @subpackage Column_System
 * @version 5.0.0 - UX Optimization: Target Audience First & Strong CTA
 */

get_header();

while (have_posts()): the_post();

// メタ情報を取得
$post_id = get_the_ID();
$read_time = get_field('estimated_read_time', $post_id);
$view_count = get_field('view_count', $post_id) ?: 0;
$difficulty = get_field('difficulty_level', $post_id);
$last_updated = get_field('last_updated', $post_id);
$key_points = get_field('key_points', $post_id);
$target_audience = get_field('target_audience', $post_id);
$categories = get_the_terms($post_id, 'column_category');
$tags = get_the_terms($post_id, 'column_tag');

// SEO用データ
$post_url = get_permalink();
$post_title = get_the_title();
$post_excerpt = get_the_excerpt();
$post_image = get_the_post_thumbnail_url($post_id, 'full');
$post_date = get_the_date('c');
$post_modified = get_the_modified_date('c');
$author_name = get_the_author();

// 関連コラムを取得
$related_query = new WP_Query(array(
    'post_type' => 'column',
    'posts_per_page' => 3,
    'post__not_in' => array($post_id),
    'post_status' => 'publish',
    'orderby' => 'rand',
));

// 関連補助金を取得
// 優先度1: ACFの「related_grants」フィールドから取得
$acf_related_grants = get_field('related_grants', $post_id);
$related_grants_query = null;

if (!empty($acf_related_grants) && is_array($acf_related_grants)) {
    // ACFで手動設定された関連補助金がある場合
    $related_grants_query = new WP_Query(array(
        'post_type' => 'grant',
        'post__in' => $acf_related_grants,
        'posts_per_page' => 4,
        'post_status' => 'publish',
        'orderby' => 'post__in', // ACFの順序を維持
    ));
} else {
    // 優先度2: カテゴリベースの自動関連付け
    $related_grants_args = array(
        'post_type' => 'grant',
        'posts_per_page' => 4,
        'post_status' => 'publish',
        'orderby' => 'rand',
    );
    
    // カテゴリーがあれば関連付ける
    if ($categories && !is_wp_error($categories) && !empty($categories)) {
        $category_names = array_map(function($cat) {
            return $cat->name;
        }, $categories);
        
        // コラムカテゴリーに基づいて助成金カテゴリーを検索
        $related_grants_args['tax_query'] = array(
            array(
                'taxonomy' => 'grant_category',
                'field' => 'name',
                'terms' => $category_names,
                'operator' => 'IN'
            )
        );
    }
    
    $related_grants_query = new WP_Query($related_grants_args);
}
?>

<!-- SEO: 構造化データ - パンくずリスト -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "ホーム",
      "item": "<?php echo home_url('/'); ?>"
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "コラム",
      "item": "<?php echo get_post_type_archive_link('column'); ?>"
    }
    <?php if ($categories && !is_wp_error($categories)): ?>
    ,{
      "@type": "ListItem",
      "position": 3,
      "name": "<?php echo esc_js($categories[0]->name); ?>",
      "item": "<?php echo get_term_link($categories[0]); ?>"
    }
    <?php endif; ?>
  ]
}
</script>

<!-- SEO: 構造化データ - 記事 -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "<?php echo esc_js($post_title); ?>",
  "description": "<?php echo esc_js($post_excerpt); ?>",
  "image": "<?php echo esc_url($post_image); ?>",
  "datePublished": "<?php echo $post_date; ?>",
  "dateModified": "<?php echo $post_modified; ?>",
  "author": {
    "@type": "Person",
    "name": "<?php echo esc_js($author_name); ?>"
  },
  "publisher": {
    "@type": "Organization",
    "name": "<?php echo esc_js(get_bloginfo('name')); ?>",
    "logo": {
      "@type": "ImageObject",
      "url": "<?php echo esc_url(get_site_icon_url()); ?>"
    }
  },
  "mainEntityOfPage": {
    "@type": "WebPage",
    "@id": "<?php echo esc_url($post_url); ?>"
  }
  <?php if ($read_time): ?>
  ,"timeRequired": "PT<?php echo intval($read_time); ?>M"
  <?php endif; ?>
}
</script>

<!-- SEO: OGPメタタグ -->
<?php if (!function_exists('wp_head_has_ogp')): ?>
<meta property="og:type" content="article">
<meta property="og:title" content="<?php echo esc_attr($post_title); ?>">
<meta property="og:description" content="<?php echo esc_attr($post_excerpt); ?>">
<meta property="og:url" content="<?php echo esc_url($post_url); ?>">
<meta property="og:image" content="<?php echo esc_url($post_image); ?>">
<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
<meta property="article:published_time" content="<?php echo $post_date; ?>">
<meta property="article:modified_time" content="<?php echo $post_modified; ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr($post_title); ?>">
<meta name="twitter:description" content="<?php echo esc_attr($post_excerpt); ?>">
<meta name="twitter:image" content="<?php echo esc_url($post_image); ?>">
<?php endif; ?>

<!-- Single Column - SEO Enhanced -->
<article id="post-<?php the_ID(); ?>" <?php post_class('single-column-seo-enhanced'); ?> itemscope itemtype="https://schema.org/Article">
    
    <!-- SEO: 非表示のメタデータ -->
    <meta itemprop="headline" content="<?php echo esc_attr($post_title); ?>">
    <meta itemprop="description" content="<?php echo esc_attr($post_excerpt); ?>">
    <meta itemprop="image" content="<?php echo esc_url($post_image); ?>">
    <meta itemprop="datePublished" content="<?php echo $post_date; ?>">
    <meta itemprop="dateModified" content="<?php echo $post_modified; ?>">
    <div itemprop="author" itemscope itemtype="https://schema.org/Person" style="display:none;">
        <span itemprop="name"><?php echo esc_html($author_name); ?></span>
    </div>
    <div itemprop="publisher" itemscope itemtype="https://schema.org/Organization" style="display:none;">
        <span itemprop="name"><?php echo esc_html(get_bloginfo('name')); ?></span>
        <div itemprop="logo" itemscope itemtype="https://schema.org/ImageObject">
            <meta itemprop="url" content="<?php echo esc_url(get_site_icon_url()); ?>">
        </div>
    </div>
    
    <div class="column-layout-container">
        
        <!-- メインコンテンツ -->
        <main class="column-main-content" role="main">
            
            <!-- ヘッダーセクション -->
            <header class="column-header-section">
                
                <!-- パンくずリスト -->
                <nav class="column-breadcrumb" aria-label="パンくずナビゲーション">
                    <ol itemscope itemtype="https://schema.org/BreadcrumbList">
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a itemprop="item" href="<?php echo home_url('/'); ?>">
                                <span itemprop="name">ホーム</span>
                            </a>
                            <meta itemprop="position" content="1">
                        </li>
                        <li><i class="fas fa-chevron-right" aria-hidden="true"></i></li>
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                            <a itemprop="item" href="<?php echo get_post_type_archive_link('column'); ?>">
                                <span itemprop="name">コラム</span>
                            </a>
                            <meta itemprop="position" content="2">
                        </li>
                        <?php if ($categories && !is_wp_error($categories)): ?>
                            <li><i class="fas fa-chevron-right" aria-hidden="true"></i></li>
                            <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <a itemprop="item" href="<?php echo get_term_link($categories[0]); ?>">
                                    <span itemprop="name"><?php echo esc_html($categories[0]->name); ?></span>
                                </a>
                                <meta itemprop="position" content="3">
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>

                <!-- カテゴリバッジ -->
                <div class="column-badges">
                    <?php if ($categories && !is_wp_error($categories)): ?>
                        <?php foreach (array_slice($categories, 0, 2) as $cat): ?>
                            <a href="<?php echo get_term_link($cat); ?>" class="badge badge-category" rel="category tag">
                                <i class="fas fa-folder" aria-hidden="true"></i>
                                <?php echo esc_html($cat->name); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if ($difficulty): ?>
                        <?php
                        $difficulty_labels = array(
                            'beginner' => array('label' => '初級', 'class' => 'badge-beginner'),
                            'intermediate' => array('label' => '中級', 'class' => 'badge-intermediate'),
                            'advanced' => array('label' => '上級', 'class' => 'badge-advanced'),
                        );
                        $diff_info = $difficulty_labels[$difficulty] ?? array('label' => $difficulty, 'class' => 'badge-default');
                        ?>
                        <span class="badge <?php echo $diff_info['class']; ?>" aria-label="難易度: <?php echo $diff_info['label']; ?>">
                            <i class="fas fa-signal" aria-hidden="true"></i>
                            <?php echo $diff_info['label']; ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- タイトル -->
                <h1 class="column-title" itemprop="headline"><?php the_title(); ?></h1>

                <!-- メタ情報 -->
                <div class="column-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <time datetime="<?php echo get_the_date('c'); ?>" itemprop="datePublished">
                            <?php echo get_the_date('Y年m月d日'); ?>
                        </time>
                    </div>
                    
                    <?php if ($last_updated && $last_updated !== get_the_date('Y-m-d')): ?>
                        <div class="meta-item">
                            <i class="fas fa-sync-alt" aria-hidden="true"></i>
                            <time datetime="<?php echo date('c', strtotime($last_updated)); ?>" itemprop="dateModified">
                                更新: <?php echo date('Y年m月d日', strtotime($last_updated)); ?>
                            </time>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($read_time): ?>
                        <div class="meta-item">
                            <i class="fas fa-clock" aria-hidden="true"></i>
                            <span><?php echo esc_html($read_time); ?>分</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="meta-item">
                        <i class="fas fa-eye" aria-hidden="true"></i>
                        <span><?php echo number_format($view_count); ?>回閲覧</span>
                    </div>
                </div>

            </header>

            <!-- 対象読者（結論ファースト：記事の最上部に配置） -->
            <?php if ($target_audience && is_array($target_audience) && count($target_audience) > 0): ?>
                <aside class="target-audience-box" aria-label="対象読者">
                    <h2 class="box-title">
                        <i class="fas fa-users" aria-hidden="true"></i>
                        この記事はこんな方におすすめ
                    </h2>
                    <ul class="audience-list">
                        <?php
                        $audience_labels = array(
                            'startup' => '創業・スタートアップを考えている方',
                            'sme' => '中小企業の経営者・担当者',
                            'individual' => '個人事業主・フリーランス',
                            'npo' => 'NPO・一般社団法人',
                            'agriculture' => '農業・林業・漁業従事者',
                            'other' => 'その他事業者',
                        );
                        foreach ($target_audience as $audience):
                            if (isset($audience_labels[$audience])):
                        ?>
                            <li><i class="fas fa-check" aria-hidden="true"></i><?php echo esc_html($audience_labels[$audience]); ?></li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </aside>
            <?php endif; ?>

            <!-- アイキャッチ画像 -->
            <?php if (has_post_thumbnail()): ?>
                <figure class="column-thumbnail" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                    <?php the_post_thumbnail('large', array('itemprop' => 'url contentUrl')); ?>
                    <meta itemprop="width" content="<?php echo get_the_post_thumbnail_url($post_id, 'large') ? '1200' : ''; ?>">
                    <meta itemprop="height" content="<?php echo get_the_post_thumbnail_url($post_id, 'large') ? '630' : ''; ?>">
                </figure>
            <?php endif; ?>

            <!-- 記事本文 -->
            <div class="column-content" itemprop="articleBody">
                <?php the_content(); ?>
            </div>

            <!-- 記事終了後の最強CTAボックス - UX最適化版（AI診断 + 補助金検索） -->
            <section class="gus-cta-section" style="margin-top: var(--gus-space-2xl); margin-bottom: var(--gus-space-2xl);" role="complementary" aria-label="次のアクション">
                <div class="gus-cta-container">
                    <div class="gus-cta-content">
                        <div class="gus-cta-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                <circle cx="12" cy="12" r="2" fill="currentColor"/>
                            </svg>
                        </div>
                        <h2 class="gus-cta-title">
                            あなたに合う補助金・助成金を今すぐ見つけましょう
                        </h2>
                        <p class="gus-cta-description">
                            AI診断で最適な補助金を提案。<br>
                            助成金インサイトであなたのビジネスに最適な支援制度を見つけましょう。
                        </p>
                        <div class="gus-cta-buttons">
                            <a href="<?php echo home_url('/subsidy-diagnosis/'); ?>" 
                               class="gus-cta-btn gus-cta-btn-primary"
                               aria-label="AIで最適な補助金を診断">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 11l3 3L22 4"/>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                </svg>
                                <span>
                                    <strong>AIで診断する</strong>
                                    <small>あなたに最適な補助金を提案</small>
                                </span>
                            </a>
                            <a href="<?php echo home_url('/grants/'); ?>" 
                               class="gus-cta-btn gus-cta-btn-secondary"
                               aria-label="補助金一覧から探す">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"/>
                                    <path d="m21 21-4.35-4.35"/>
                                </svg>
                                <span>
                                    <strong>一覧から探す</strong>
                                    <small>全ての補助金をチェック</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- タグ -->
            <?php if ($tags && !is_wp_error($tags)): ?>
                <nav class="column-tags" aria-label="タグ">
                    <h2 class="tags-title">
                        <i class="fas fa-tags" aria-hidden="true"></i>
                        タグ
                    </h2>
                    <div class="tags-list">
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?php echo get_term_link($tag); ?>" class="tag-link" rel="tag">
                                #<?php echo esc_html($tag->name); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </nav>
            <?php endif; ?>

            <!-- シェアボタン -->
            <aside class="column-share" aria-label="シェアボタン">
                <h2 class="share-title">この記事をシェア</h2>
                <div class="share-buttons">
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($post_url); ?>&text=<?php echo urlencode($post_title); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="share-btn share-twitter"
                       aria-label="Twitterでシェア">
                        <i class="fab fa-twitter" aria-hidden="true"></i>
                        Twitter
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($post_url); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="share-btn share-facebook"
                       aria-label="Facebookでシェア">
                        <i class="fab fa-facebook-f" aria-hidden="true"></i>
                        Facebook
                    </a>
                    <a href="https://social-plugins.line.me/lineit/share?url=<?php echo urlencode($post_url); ?>" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="share-btn share-line"
                       aria-label="LINEでシェア">
                        <i class="fab fa-line" aria-hidden="true"></i>
                        LINE
                    </a>
                </div>
            </aside>

            <!-- スマホ用: 関連する補助金 -->
            <?php if ($related_grants_query && $related_grants_query->have_posts()): ?>
            <section class="mobile-related-grants" aria-labelledby="mobile-related-grants-title">
                <h2 class="section-title" id="mobile-related-grants-title">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                    </svg>
                    この記事に関連する補助金
                </h2>
                <div class="mobile-grants-grid">
                    <?php 
                    // モバイル用は2件表示
                    $count = 0;
                    while ($related_grants_query->have_posts() && $count < 2): $related_grants_query->the_post(); 
                        $grant_id = get_the_ID();
                        $grant_amount = get_field('max_amount_numeric', $grant_id);
                        $grant_deadline = get_field('deadline', $grant_id);
                        
                        $formatted_amount = '';
                        if ($grant_amount && $grant_amount > 0) {
                            if ($grant_amount >= 10000) {
                                $formatted_amount = number_format($grant_amount / 10000) . '万円';
                            } else {
                                $formatted_amount = number_format($grant_amount) . '円';
                            }
                        }
                    ?>
                        <article class="mobile-grant-card">
                            <a href="<?php the_permalink(); ?>" class="mobile-grant-link">
                                <span class="mobile-grant-badge">補助金</span>
                                <h3 class="mobile-grant-title"><?php the_title(); ?></h3>
                                <div class="mobile-grant-info">
                                    <?php if ($formatted_amount): ?>
                                        <span class="mobile-grant-amount">上限 <?php echo esc_html($formatted_amount); ?></span>
                                    <?php endif; ?>
                                    <?php if ($grant_deadline): ?>
                                        <span class="mobile-grant-deadline"><?php echo esc_html($grant_deadline); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="mobile-grant-cta">詳細を見る →</span>
                            </a>
                        </article>
                    <?php 
                        $count++;
                    endwhile; 
                    wp_reset_postdata(); 
                    ?>
                </div>
                <a href="<?php echo home_url('/grants/'); ?>" class="mobile-view-all-grants">
                    すべての補助金を見る
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </section>
            <?php endif; ?>

            <!-- 関連記事 -->
            <?php if ($related_query->have_posts()): ?>
                <section class="related-columns" aria-labelledby="related-title">
                    <h2 class="related-title" id="related-title">
                        <i class="fas fa-newspaper" aria-hidden="true"></i>
                        関連コラム
                    </h2>
                    <div class="related-grid">
                        <?php while ($related_query->have_posts()): $related_query->the_post(); ?>
                            <?php get_template_part('template-parts/column/card'); ?>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                </section>
            <?php endif; ?>

        </main>

        <!-- AIサイドバー（ワイド版・常時表示） -->
        <aside class="column-sidebar" role="complementary" aria-label="サイドバー">
            
            <!-- アフィリエイト広告: サイドバー上部 -->
            <?php if (function_exists('ji_display_ad')): ?>
                <div class="sidebar-ad-space sidebar-ad-top">
                    <?php ji_display_ad('single_column_sidebar_top', 'single-column'); ?>
                </div>
            <?php endif; ?>

            <!-- 目次カード -->
            <section class="sidebar-card toc-card" aria-labelledby="toc-card-title">
                <header class="card-header">
                    <i class="fas fa-list" aria-hidden="true"></i>
                    <h2 id="toc-card-title">目次</h2>
                </header>
                <div class="card-body">
                    <nav class="toc-nav" id="toc-nav" aria-label="記事の目次">
                        <!-- JavaScriptで動的生成 -->
                    </nav>
                </div>
            </section>
            

            
            <!-- 関連する補助金 -->
            <?php if ($related_grants_query && $related_grants_query->have_posts()): ?>
            <section class="sidebar-card related-grants-card" aria-labelledby="related-grants-title">
                <header class="card-header">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                    </svg>
                    <h2 id="related-grants-title">関連する補助金</h2>
                </header>
                <div class="card-body">
                    <div class="related-grants-list">
                        <?php while ($related_grants_query->have_posts()): $related_grants_query->the_post(); 
                            $grant_id = get_the_ID();
                            $grant_amount = get_field('max_amount_numeric', $grant_id);
                            $grant_deadline = get_field('deadline', $grant_id);
                            $grant_status = get_field('application_status', $grant_id);
                            
                            $formatted_amount = '';
                            if ($grant_amount && $grant_amount > 0) {
                                if ($grant_amount >= 10000) {
                                    $formatted_amount = number_format($grant_amount / 10000) . '万円';
                                } else {
                                    $formatted_amount = number_format($grant_amount) . '円';
                                }
                            }
                        ?>
                            <article class="related-grant-item">
                                <a href="<?php the_permalink(); ?>" class="related-grant-link">
                                    <h3 class="related-grant-title"><?php the_title(); ?></h3>
                                    <div class="related-grant-meta">
                                        <?php if ($formatted_amount): ?>
                                            <span class="grant-amount">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <line x1="12" y1="1" x2="12" y2="23"/>
                                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                                </svg>
                                                <?php echo esc_html($formatted_amount); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($grant_deadline): ?>
                                            <span class="grant-deadline">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                                    <line x1="8" y1="2" x2="8" y2="6"/>
                                                    <line x1="3" y1="10" x2="21" y2="10"/>
                                                </svg>
                                                <?php echo esc_html($grant_deadline); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($grant_status === 'open'): ?>
                                        <span class="grant-status status-open">募集中</span>
                                    <?php endif; ?>
                                </a>
                            </article>
                        <?php endwhile; wp_reset_postdata(); ?>
                    </div>
                    <a href="<?php echo home_url('/grants/'); ?>" class="view-all-grants">
                        すべての補助金を見る
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </a>
                </div>
            </section>
            <?php endif; ?>

            <!-- 人気記事カード -->
            <section class="sidebar-card popular-card" aria-labelledby="popular-card-title">
                <header class="card-header">
                    <i class="fas fa-fire" aria-hidden="true"></i>
                    <h2 id="popular-card-title">人気のコラム</h2>
                </header>
                <div class="card-body">
                    <?php
                    $popular_query = new WP_Query(array(
                        'post_type' => 'column',
                        'posts_per_page' => 5,
                        'meta_key' => 'view_count',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC',
                    ));
                    
                    if ($popular_query->have_posts()):
                    ?>
                        <ul class="popular-list">
                            <?php while ($popular_query->have_posts()): $popular_query->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>">
                                        <span class="popular-rank" aria-label="ランキング <?php echo $popular_query->current_post + 1; ?>位"><?php echo $popular_query->current_post + 1; ?></span>
                                        <span class="popular-title"><?php the_title(); ?></span>
                                    </a>
                                </li>
                            <?php endwhile; wp_reset_postdata(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>

            <!-- アフィリエイト広告: サイドバー下部 -->
            <?php if (function_exists('ji_display_ad')): ?>
                <div class="sidebar-ad-space sidebar-ad-bottom">
                    <?php ji_display_ad('single_column_sidebar_bottom', 'single-column'); ?>
                </div>
            <?php endif; ?>

        </aside>

    </div>

</article>

<!-- モバイル用統合ナビCTAボタン -->
<button class="gus-mobile-toc-cta" id="mobileTocBtn" aria-label="目次とAI質問を開く">
    <div class="gus-mobile-toc-icon">
        <span class="gus-mobile-toc-icon-toc" aria-hidden="true">📑</span>
        <span class="gus-mobile-toc-icon-ai">AI</span>
    </div>
</button>

<!-- モバイル用目次オーバーレイ -->
<div class="gus-mobile-toc-overlay" id="mobileTocOverlay" aria-hidden="true"></div>

<!-- モバイル用統合ナビパネル -->
<div class="gus-mobile-toc-panel" id="mobileTocPanel" role="dialog" aria-labelledby="mobile-panel-title" aria-modal="true">
    <header class="gus-mobile-toc-header">
        <h2 class="gus-mobile-toc-title" id="mobile-panel-title">目次 & AI質問</h2>
        <button class="gus-mobile-toc-close" id="mobileTocClose" aria-label="閉じる">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </header>
    
    <!-- タブナビゲーション -->
    <div class="gus-mobile-nav-tabs" role="tablist" aria-label="目次とAI質問の切り替え">
        <button class="gus-mobile-nav-tab active" data-tab="ai" role="tab" aria-selected="true" aria-controls="aiContent" id="aiTab">
            <i class="fas fa-robot" aria-hidden="true"></i>
            AI 質問
        </button>
        <button class="gus-mobile-nav-tab" data-tab="toc" role="tab" aria-selected="false" aria-controls="tocContent" id="tocTab">
            <i class="fas fa-list" aria-hidden="true"></i>
            📑 目次
        </button>
    </div>
    
    <!-- AI質問コンテンツ -->
    <div class="gus-mobile-nav-content active" id="aiContent" role="tabpanel" aria-labelledby="aiTab">
        <div class="gus-ai-chat-messages" id="mobileAiMessages" role="log" aria-live="polite" aria-label="AIチャット">
            <div class="ai-message ai-message-assistant">
                <div class="ai-avatar" aria-hidden="true">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-content">
                    こんにちは！この記事について何でも質問してください。
                </div>
            </div>
        </div>
        <div class="gus-ai-input-container">
            <label for="mobileAiInput" class="sr-only">AI質問入力</label>
            <textarea id="mobileAiInput" 
                      placeholder="例：この補助金の申請期限は？" 
                      rows="2"
                      aria-label="AI質問入力"></textarea>
            <button id="mobileAiSend" class="gus-ai-send-btn" aria-label="質問を送信">
                <i class="fas fa-paper-plane" aria-hidden="true"></i>
                送信
            </button>
        </div>
    </div>
    
    <!-- 目次コンテンツ -->
    <div class="gus-mobile-nav-content" id="tocContent" role="tabpanel" aria-labelledby="tocTab" hidden>
        <nav class="gus-mobile-toc-list" id="mobileTocList" aria-label="記事の目次">
            <!-- JavaScriptで動的生成 -->
        </nav>
    </div>
</div>

<?php endwhile; ?>

<?php get_footer(); ?>

<style>
/* ============================================
   Single Column - SEO Enhanced & Wide Sidebar
   コラム詳細 - SEO最適化 + ワイドサイドバー
   ============================================ */

:root {
    --color-primary: #000000;
    --color-secondary: #ffffff;
    --color-accent: #ffeb3b;
    --color-gray-50: #fafafa;
    --color-gray-100: #f5f5f5;
    --color-gray-200: #e5e5e5;
    --color-gray-600: #525252;
    --color-gray-900: #171717;
    --sidebar-width: 420px; /* 360px → 420px に拡大 */
    --header-height: 80px;
}

/* スクリーンリーダー専用クラス */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}

.single-column-seo-enhanced {
    background: var(--color-gray-50);
    min-height: 100vh;
}

/* レイアウトコンテナ */
.column-layout-container {
    max-width: 1480px; /* 1400px → 1480px に拡大（サイドバー幅増加分） */
    margin: 0 auto;
    padding: 32px 16px;
    display: grid;
    grid-template-columns: 1fr;
    gap: 40px; /* 32px → 40px に拡大 */
}

@media (min-width: 1024px) {
    .column-layout-container {
        grid-template-columns: 1fr var(--sidebar-width);
        align-items: start;
    }
}

/* メインコンテンツ */
.column-main-content {
    background: var(--color-secondary);
    border: 3px solid var(--color-primary);
    padding: 40px 32px; /* 32px 24px → 40px 32px に拡大 */
}

@media (max-width: 767px) {
    .column-main-content {
        padding: 24px 20px;
    }
}

/* ヘッダーセクション */
.column-header-section {
    margin-bottom: 40px; /* 32px → 40px */
    padding-bottom: 32px; /* 24px → 32px */
    border-bottom: 2px solid var(--color-gray-200);
}

/* パンくずリスト */
.column-breadcrumb {
    margin-bottom: 20px; /* 16px → 20px */
}

.column-breadcrumb ol {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    font-size: 14px;
    color: var(--color-gray-600);
    flex-wrap: wrap;
}

.column-breadcrumb a {
    color: var(--color-gray-600);
    text-decoration: none;
    transition: color 0.2s;
}

.column-breadcrumb a:hover,
.column-breadcrumb a:focus {
    color: var(--color-primary);
    text-decoration: underline;
}

.column-breadcrumb i {
    font-size: 10px;
}

/* バッジ */
.column-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* 8px → 10px */
    margin-bottom: 20px; /* 16px → 20px */
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px; /* 6px 14px → 8px 16px */
    font-size: 14px; /* 13px → 14px */
    font-weight: 700;
    border: 2px solid;
    text-decoration: none;
    transition: all 0.2s;
}

.badge:hover,
.badge:focus {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.badge i {
    font-size: 12px;
}

.badge-category {
    background: var(--color-primary);
    color: var(--color-accent);
    border-color: var(--color-primary);
}

.badge-beginner {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.badge-intermediate {
    background: #f59e0b;
    color: white;
    border-color: #f59e0b;
}

.badge-advanced {
    background: #ef4444;
    color: white;
    border-color: #ef4444;
}

/* タイトル */
.column-title {
    font-size: 36px; /* 32px → 36px */
    font-weight: 900;
    color: var(--color-primary);
    line-height: 1.4;
    margin: 0 0 20px; /* 16px → 20px */
}

@media (max-width: 767px) {
    .column-title {
        font-size: 26px; /* 24px → 26px */
    }
}

/* メタ情報 */
.column-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* 16px → 20px */
    font-size: 15px; /* 14px → 15px */
    color: var(--color-gray-600);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.meta-item i {
    color: var(--color-primary);
}

/* アイキャッチ */
.column-thumbnail {
    margin: 32px 0; /* 24px → 32px */
    border: 2px solid var(--color-primary);
    overflow: hidden;
}

.column-thumbnail img {
    width: 100%;
    height: auto;
    display: block;
}

/* 対象読者ボックス */
.target-audience-box {
    background: var(--color-gray-50);
    border-left: 4px solid var(--color-primary);
    padding: 24px; /* 20px → 24px */
    margin: 32px 0; /* 24px → 32px */
}

.box-title {
    font-size: 18px; /* 16px → 18px */
    font-weight: 700;
    color: var(--color-primary);
    margin: 0 0 16px; /* 12px → 16px */
    display: flex;
    align-items: center;
    gap: 10px;
}

.audience-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px; /* 8px → 10px */
}

.audience-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px; /* 14px → 15px */
    color: var(--color-gray-600);
}

.audience-list i {
    color: var(--color-primary);
}

/* 記事本文 */
.column-content {
    font-size: 17px; /* 16px → 17px */
    line-height: 1.9; /* 1.8 → 1.9 */
    color: var(--color-gray-900);
    margin: 40px 0; /* 32px → 40px */
}

.column-content h2 {
    font-size: 26px; /* 24px → 26px */
    font-weight: 700;
    margin: 40px 0 20px; /* 32px 0 16px → 40px 0 20px */
    padding-bottom: 12px; /* 8px → 12px */
    border-bottom: 3px solid var(--color-primary);
}

.column-content h3 {
    font-size: 22px; /* 20px → 22px */
    font-weight: 700;
    margin: 32px 0 16px; /* 24px 0 12px → 32px 0 16px */
}

.column-content p {
    margin: 20px 0; /* 16px → 20px */
}

.column-content ul,
.column-content ol {
    margin: 20px 0; /* 16px → 20px */
    padding-left: 28px; /* 24px → 28px */
}

.column-content li {
    margin: 10px 0; /* 8px → 10px */
}

@media (max-width: 767px) {
    .column-content {
        font-size: 16px; /* 15px → 16px */
    }
}

/* タグ */
.column-tags {
    margin: 40px 0; /* 32px → 40px */
    padding: 24px; /* 20px → 24px */
    background: var(--color-gray-50);
    border: 2px solid var(--color-gray-200);
}

.tags-title {
    font-size: 18px; /* 16px → 18px */
    font-weight: 700;
    margin: 0 0 16px; /* 12px → 16px */
    display: flex;
    align-items: center;
    gap: 10px;
}

.tags-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* 8px → 10px */
}

.tag-link {
    display: inline-block;
    padding: 8px 16px; /* 6px 12px → 8px 16px */
    font-size: 14px; /* 13px → 14px */
    font-weight: 600;
    color: var(--color-primary);
    background: var(--color-secondary);
    border: 1px solid var(--color-primary);
    text-decoration: none;
    transition: all 0.2s;
}

.tag-link:hover,
.tag-link:focus {
    background: var(--color-accent);
    transform: translateY(-2px);
}

/* 最強CTAボックス - 記事終了後の行動誘導（UX最適化版） */
.gus-cta-section {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    color: #ffffff;
    padding: 64px 0;
    position: relative;
    overflow: hidden;
}

.gus-cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
}

.gus-cta-section::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
}

.gus-cta-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--gus-space-xl, 32px);
}

.gus-cta-content {
    text-align: center;
}

.gus-cta-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    background: rgba(255, 215, 0, 0.1);
    border-radius: 50%;
    margin-bottom: var(--gus-space-lg, 24px);
    color: #FFD700;
}

.gus-cta-icon svg {
    width: 48px;
    height: 48px;
}

.gus-cta-title {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: var(--gus-space-lg, 24px);
    color: #ffffff;
}

.gus-cta-description {
    font-size: 1.125rem;
    line-height: 1.6;
    margin-bottom: var(--gus-space-2xl, 48px);
    color: rgba(255, 255, 255, 0.9);
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

.gus-cta-buttons {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--gus-space-lg, 24px);
    max-width: 900px;
    margin: 0 auto;
}

.gus-cta-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--gus-space-md, 16px);
    padding: 24px 32px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    min-height: 90px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.gus-cta-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.gus-cta-btn:hover::before {
    left: 100%;
}

.gus-cta-btn svg {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    transition: transform 0.3s ease;
}

.gus-cta-btn span {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
    text-align: left;
}

.gus-cta-btn strong {
    font-size: 1.125rem;
    font-weight: 700;
    display: block;
}

.gus-cta-btn small {
    font-size: 0.875rem;
    font-weight: 400;
    opacity: 0.9;
    display: block;
}

/* Primary CTA Button - Black with Yellow Accent */
.gus-cta-btn-primary {
    background: #000000;
    color: #ffffff;
    border: 2px solid #FFD700;
}

.gus-cta-btn-primary:hover {
    background: #FFD700;
    color: #000000;
    border-color: #FFD700;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(255, 215, 0, 0.4);
}

.gus-cta-btn-primary:hover svg {
    transform: scale(1.1) rotate(5deg);
}

/* Secondary CTA Button - White with Black Text */
.gus-cta-btn-secondary {
    background: #ffffff;
    color: #000000;
    border: 2px solid #e5e5e5;
}

.gus-cta-btn-secondary:hover {
    background: #000000;
    color: #ffffff;
    border-color: #000000;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.gus-cta-btn-secondary:hover svg {
    transform: scale(1.1);
}

/* Tablet Responsive */
@media (max-width: 1024px) {
    .gus-cta-section {
        padding: 56px 0;
    }
    
    .gus-cta-title {
        font-size: 1.75rem;
    }
    
    .gus-cta-description {
        font-size: 1rem;
    }
    
    .gus-cta-buttons {
        gap: var(--gus-space-md, 16px);
    }
    
    .gus-cta-btn {
        padding: 20px 24px;
        min-height: 80px;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .gus-cta-section {
        padding: 48px 0;
    }
    
    .gus-cta-container {
        padding: 0 var(--gus-space-lg, 24px);
    }
    
    .gus-cta-icon {
        width: 64px;
        height: 64px;
    }
    
    .gus-cta-icon svg {
        width: 40px;
        height: 40px;
    }
    
    .gus-cta-title {
        font-size: 1.5rem;
        line-height: 1.3;
        margin-bottom: var(--gus-space-md, 16px);
    }
    
    .gus-cta-description {
        font-size: 0.9375rem;
        margin-bottom: var(--gus-space-xl, 32px);
    }
    
    .gus-cta-buttons {
        grid-template-columns: 1fr;
        gap: var(--gus-space-md, 16px);
        max-width: 100%;
    }
    
    .gus-cta-btn {
        padding: 18px 20px;
        min-height: 70px;
        font-size: 0.9375rem;
    }
    
    .gus-cta-btn strong {
        font-size: 1rem;
    }
    
    .gus-cta-btn small {
        font-size: 0.8125rem;
    }
}

/* Extra Small Mobile */
@media (max-width: 375px) {
    .gus-cta-section {
        padding: 40px 0;
    }
    
    .gus-cta-title {
        font-size: 1.25rem;
    }
    
    .gus-cta-description {
        font-size: 0.875rem;
    }
    
    .gus-cta-btn {
        padding: 16px 18px;
        min-height: 65px;
        gap: var(--gus-space-sm, 12px);
    }
    
    .gus-cta-btn svg {
        width: 20px;
        height: 20px;
    }
}

/* Reduced Motion Support */
@media (prefers-reduced-motion: reduce) {
    .gus-cta-btn,
    .gus-cta-btn svg,
    .gus-cta-btn::before {
        transition: none;
    }
    
    .gus-cta-btn:hover {
        transform: none;
    }
    
    .gus-cta-btn:hover svg {
        transform: none;
    }
}

/* High Contrast Mode Support */
@media (prefers-contrast: high) {
    .gus-cta-section {
        background: #000000;
        border-top: 4px solid #FFD700;
        border-bottom: 4px solid #FFD700;
    }
    
    .gus-cta-btn-primary {
        border-width: 3px;
    }
    
    .gus-cta-btn-secondary {
        border-width: 3px;
    }
}

/* シェアボタン */
.column-share {
    margin: 40px 0; /* 32px → 40px */
    padding: 28px; /* 24px → 28px */
    background: var(--color-primary);
    color: var(--color-secondary);
    text-align: center;
}

.share-title {
    font-size: 18px; /* 16px → 18px */
    font-weight: 700;
    margin: 0 0 20px; /* 16px → 20px */
}

.share-buttons {
    display: flex;
    justify-content: center;
    gap: 16px; /* 12px → 16px */
    flex-wrap: wrap;
}

.share-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px; /* 8px → 10px */
    padding: 12px 24px; /* 10px 20px → 12px 24px */
    font-size: 15px; /* 14px → 15px */
    font-weight: 600;
    border: 2px solid var(--color-secondary);
    text-decoration: none;
    transition: all 0.2s;
}

.share-twitter {
    background: #1DA1F2;
    color: white;
    border-color: #1DA1F2;
}

.share-facebook {
    background: #4267B2;
    color: white;
    border-color: #4267B2;
}

.share-line {
    background: #00B900;
    color: white;
    border-color: #00B900;
}

.share-btn:hover,
.share-btn:focus {
    transform: translateY(-2px);
    opacity: 0.9;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* 関連記事 */
.related-columns {
    margin: 56px 0 0; /* 48px → 56px */
    padding: 40px 0 0; /* 32px → 40px */
    border-top: 3px solid var(--color-primary);
}

.related-title {
    font-size: 22px; /* 20px → 22px */
    font-weight: 700;
    margin: 0 0 28px; /* 24px → 28px */
    display: flex;
    align-items: center;
    gap: 10px;
}

.related-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px; /* 20px → 24px */
}

@media (min-width: 640px) {
    .related-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .related-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

/* ============================================
   AIサイドバー（ワイド版・常時表示最適化）
   ============================================ */

.column-sidebar {
    display: flex;
    flex-direction: column;
    gap: 28px; /* 24px → 28px */
}

/* デスクトップ: スティッキーサイドバー（最適化） */
@media (min-width: 1024px) {
    .column-sidebar {
        position: sticky;
        top: calc(var(--header-height) + 20px); /* ヘッダー高さ + 余白 */
        overflow-y: auto;
        overflow-x: hidden;
        align-self: flex-start;
        
        /* スクロールバーのスタイリング */
        scrollbar-width: thin;
        scrollbar-color: var(--color-gray-200) transparent;
    }
    
    .column-sidebar::-webkit-scrollbar {
        width: 6px;
    }
    
    .column-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .column-sidebar::-webkit-scrollbar-thumb {
        background-color: var(--color-gray-200);
        border-radius: 3px;
    }
    
    .column-sidebar::-webkit-scrollbar-thumb:hover {
        background-color: var(--color-gray-600);
    }
}

.sidebar-card {
    background: var(--color-secondary);
    border: 3px solid var(--color-primary);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.2s;
}

.sidebar-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.card-header {
    background: var(--color-primary);
    color: var(--color-accent);
    padding: 18px 20px; /* 16px → 18px 20px */
    display: flex;
    align-items: center;
    gap: 12px; /* 10px → 12px */
}

.card-header h2 {
    font-size: 17px; /* 16px → 17px */
    font-weight: 700;
    margin: 0;
}

.card-header i {
    font-size: 20px; /* 18px → 20px */
}

.card-body {
    padding: 24px; /* 20px → 24px */
}

/* AI相談カード */
.ai-intro {
    font-size: 15px; /* 14px → 15px */
    color: var(--color-gray-600);
    margin: 0 0 20px; /* 16px → 20px */
}

.ai-chat-container {
    max-height: 350px; /* 300px → 350px に拡大 */
    overflow-y: auto;
    margin-bottom: 20px; /* 16px → 20px */
    display: flex;
    flex-direction: column;
    gap: 14px; /* 12px → 14px */
    padding: 4px;
    
    /* スクロールバーのスタイリング */
    scrollbar-width: thin;
    scrollbar-color: var(--color-gray-200) transparent;
}

.ai-chat-container::-webkit-scrollbar {
    width: 6px;
}

.ai-chat-container::-webkit-scrollbar-track {
    background: transparent;
}

.ai-chat-container::-webkit-scrollbar-thumb {
    background-color: var(--color-gray-200);
    border-radius: 3px;
}

.ai-message {
    display: flex;
    gap: 12px; /* 10px → 12px */
}

.ai-message-assistant {
    align-self: flex-start;
}

.ai-avatar {
    width: 36px; /* 32px → 36px */
    height: 36px;
    border-radius: 50%;
    background: var(--color-primary);
    color: var(--color-accent);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ai-content {
    background: var(--color-gray-100);
    padding: 12px 16px; /* 10px 14px → 12px 16px */
    border-radius: 12px;
    font-size: 15px; /* 14px → 15px */
    line-height: 1.7; /* 1.6 → 1.7 */
    max-width: 80%;
}

.ai-input-form textarea {
    width: 100%;
    padding: 12px; /* 10px → 12px */
    border: 2px solid var(--color-primary);
    font-size: 15px; /* 14px → 15px */
    resize: none;
    margin-bottom: 10px; /* 8px → 10px */
    font-family: inherit;
    line-height: 1.5;
}

.ai-input-form textarea:focus {
    outline: none;
    border-color: var(--color-accent);
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.2);
}

.ai-send-btn {
    width: 100%;
    padding: 14px; /* 12px → 14px */
    background: var(--color-primary);
    color: var(--color-accent);
    border: none;
    font-size: 16px; /* 15px → 16px */
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.2s;
}

.ai-send-btn:hover,
.ai-send-btn:focus {
    background: var(--color-accent);
    color: var(--color-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* 目次 */
.toc-nav {
    font-size: 15px; /* 14px → 15px */
}

.toc-nav ul {
    list-style: none;
    padding: 0;
}

.toc-nav li {
    margin: 10px 0; /* 8px → 10px */
}

.toc-nav a {
    color: var(--color-gray-600);
    text-decoration: none;
    display: block;
    padding: 6px 0; /* 4px → 6px */
    transition: color 0.2s;
    line-height: 1.6;
}

.toc-nav a:hover,
.toc-nav a:focus {
    color: var(--color-primary);
    text-decoration: underline;
}

/* 人気記事リスト */
.popular-list {
    list-style: none;
}

.popular-list li {
    margin: 14px 0; /* 12px → 14px */
}

.popular-list a {
    display: flex;
    align-items: flex-start;
    gap: 14px; /* 12px → 14px */
    text-decoration: none;
    color: var(--color-gray-900);
    transition: color 0.2s;
}

.popular-list a:hover,
.popular-list a:focus {
    color: var(--color-primary);
}

.popular-rank {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px; /* 24px → 28px */
    height: 28px;
    background: var(--color-primary);
    color: var(--color-accent);
    font-size: 13px; /* 12px → 13px */
    font-weight: 700;
    flex-shrink: 0;
}

.popular-title {
    flex: 1;
    font-size: 15px; /* 14px → 15px */
    line-height: 1.6;
}

/* レスポンシブ */
@media (max-width: 1023px) {
    /* モバイル: サイドバーを非表示（モバイルパネルを使用） */
    .column-sidebar {
        display: none;
    }
}

/* ============================================
   モバイル用フローティングボタン & パネル
   ============================================ */

/* モバイルCTAボタン */
.gus-mobile-toc-cta {
    display: none;
    position: fixed;
    bottom: 80px;
    right: 16px;
    z-index: 999;
    background: var(--color-gray-900);
    color: var(--color-secondary);
    border: none;
    border-radius: 50%;
    width: 60px; /* 56px → 60px */
    height: 60px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease;
    align-items: center;
    justify-content: center;
}

.gus-mobile-toc-cta:hover,
.gus-mobile-toc-cta:focus {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
}

.gus-mobile-toc-cta:active {
    transform: scale(0.95);
}

.gus-mobile-toc-icon {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
}

.gus-mobile-toc-icon-toc {
    font-size: 18px; /* 16px → 18px */
    line-height: 1;
}

.gus-mobile-toc-icon-ai {
    font-size: 11px; /* 10px → 11px */
    font-weight: 700;
    line-height: 1;
}

/* モバイルでのみ表示 */
@media (max-width: 1023px) {
    .gus-mobile-toc-cta {
        display: flex;
    }
}

/* オーバーレイ */
.gus-mobile-toc-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6); /* 0.5 → 0.6 */
    z-index: 1000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gus-mobile-toc-overlay.active {
    display: block;
    opacity: 1;
}

/* モバイルパネル */
.gus-mobile-toc-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: var(--color-secondary);
    border-top-left-radius: 20px; /* 16px → 20px */
    border-top-right-radius: 20px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.25); /* 0.2 → 0.25 */
    z-index: 1001;
    max-height: 75vh; /* 70vh → 75vh */
    display: flex;
    flex-direction: column;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.gus-mobile-toc-panel.active {
    transform: translateY(0);
}

/* パネルヘッダー */
.gus-mobile-toc-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px; /* 16px 20px → 20px 24px */
    border-bottom: 2px solid var(--color-gray-200);
}

.gus-mobile-toc-title {
    margin: 0;
    font-size: 19px; /* 18px → 19px */
    font-weight: 700;
    color: var(--color-gray-900);
}

.gus-mobile-toc-close {
    background: transparent;
    border: none;
    color: var(--color-gray-600);
    font-size: 26px; /* 24px → 26px */
    cursor: pointer;
    padding: 0;
    width: 36px; /* 32px → 36px */
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
}

.gus-mobile-toc-close:hover,
.gus-mobile-toc-close:focus {
    color: var(--color-primary);
}

/* タブナビゲーション */
.gus-mobile-nav-tabs {
    display: flex;
    border-bottom: 2px solid var(--color-gray-200);
    background: var(--color-gray-50);
}

.gus-mobile-nav-tab {
    flex: 1;
    padding: 14px 20px; /* 12px 16px → 14px 20px */
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 16px; /* 15px → 16px */
    font-weight: 600;
    color: var(--color-gray-600);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.gus-mobile-nav-tab:hover,
.gus-mobile-nav-tab:focus {
    background: var(--color-gray-100);
}

.gus-mobile-nav-tab.active {
    color: var(--color-primary);
    background: var(--color-secondary);
    border-bottom-color: var(--color-primary);
}

/* タブコンテンツ */
.gus-mobile-nav-content {
    display: none;
    flex: 1;
    overflow-y: auto;
    padding: 24px; /* 20px → 24px */
}

.gus-mobile-nav-content.active {
    display: flex;
    flex-direction: column;
}

/* AIチャットメッセージ（モバイル） */
.gus-ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    margin-bottom: 20px; /* 16px → 20px */
    display: flex;
    flex-direction: column;
    gap: 14px; /* 12px → 14px */
}

/* AI入力コンテナ（モバイル） */
.gus-ai-input-container {
    display: flex;
    gap: 10px; /* 8px → 10px */
    padding-top: 16px; /* 12px → 16px */
    border-top: 2px solid var(--color-gray-200);
}

.gus-ai-input-container textarea {
    flex: 1;
    padding: 12px 14px; /* 10px 12px → 12px 14px */
    border: 2px solid var(--color-gray-200);
    border-radius: 10px; /* 8px → 10px */
    font-size: 15px; /* 14px → 15px */
    font-family: inherit;
    resize: none;
    line-height: 1.5;
}

.gus-ai-input-container textarea:focus {
    outline: none;
    border-color: var(--color-primary);
}

.gus-ai-send-btn {
    padding: 12px 18px; /* 10px 16px → 12px 18px */
    background: var(--color-primary);
    color: var(--color-secondary);
    border: none;
    border-radius: 10px; /* 8px → 10px */
    font-size: 15px; /* 14px → 15px */
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.gus-ai-send-btn:hover,
.gus-ai-send-btn:focus {
    background: var(--color-gray-900);
}

.gus-ai-send-btn:active {
    transform: scale(0.95);
}

/* モバイル目次リスト */
.gus-mobile-toc-list {
    display: flex;
    flex-direction: column;
    gap: 6px; /* 4px → 6px */
}

.gus-mobile-toc-list ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.gus-mobile-toc-list li {
    margin: 0;
}

.gus-mobile-toc-list a {
    display: block;
    padding: 12px 14px; /* 10px 12px → 12px 14px */
    font-size: 15px; /* 14px → 15px */
    color: var(--color-gray-900);
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
    line-height: 1.6;
}

.gus-mobile-toc-list a:hover,
.gus-mobile-toc-list a:focus {
    background: var(--color-gray-50);
    border-left-color: var(--color-primary);
}

.gus-mobile-toc-list li[data-level="2"] a {
    padding-left: 28px; /* 24px → 28px */
    font-size: 14px; /* 13px → 14px */
}

/* ========================================
   Related Grants Section
   ======================================== */

/* PC版: サイドバーの関連補助金 */
.related-grants-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.related-grants-card .card-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f0f0f0;
}

.related-grants-card .card-header svg {
    flex-shrink: 0;
    color: #0073aa;
}

.related-grants-card .card-header h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
    color: #1a1a1a;
}

.related-grants-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.related-grant-item {
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.related-grant-item:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 8px rgba(0, 115, 170, 0.1);
}

.related-grant-link {
    display: block;
    padding: 12px;
    text-decoration: none;
    color: inherit;
}

.related-grant-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin: 0 0 8px 0;
    color: #1a1a1a;
}

.related-grant-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
}

.related-grant-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.related-grant-meta svg {
    flex-shrink: 0;
}

.grant-amount {
    color: #00a32a;
    font-weight: 600;
}

.grant-deadline {
    color: #d63638;
}

.grant-status {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.status-open {
    background: #e8f5e9;
    color: #1b5e20;
}

.view-all-grants {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 16px;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 6px;
    text-decoration: none;
    color: #0073aa;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.view-all-grants:hover {
    background: #0073aa;
    color: #fff;
}

.view-all-grants svg {
    flex-shrink: 0;
}

/* スマホ版: メインコンテンツの関連補助金 */
.mobile-related-grants {
    display: none;
    margin: 40px 0;
    padding: 24px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
}

.mobile-related-grants .section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 20px 0;
    color: #1a1a1a;
}

.mobile-related-grants .section-title svg {
    flex-shrink: 0;
    color: #0073aa;
}

.mobile-grants-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
    margin-bottom: 20px;
}

.mobile-grant-card {
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.mobile-grant-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.mobile-grant-link {
    display: block;
    padding: 20px;
    text-decoration: none;
    color: inherit;
}

.mobile-grant-badge {
    display: inline-block;
    padding: 4px 10px;
    background: #0073aa;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    border-radius: 4px;
    margin-bottom: 10px;
}

.mobile-grant-title {
    font-size: 16px;
    font-weight: 700;
    line-height: 1.4;
    margin: 0 0 12px 0;
    color: #1a1a1a;
}

.mobile-grant-info {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    font-size: 13px;
    margin-bottom: 12px;
}

.mobile-grant-amount {
    color: #00a32a;
    font-weight: 600;
}

.mobile-grant-deadline {
    color: #d63638;
}

.mobile-grant-cta {
    display: inline-flex;
    align-items: center;
    color: #0073aa;
    font-size: 14px;
    font-weight: 600;
}

.mobile-view-all-grants {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px;
    background: #0073aa;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    transition: all 0.2s ease;
}

.mobile-view-all-grants:hover {
    background: #005177;
}

.mobile-view-all-grants svg {
    flex-shrink: 0;
}

/* レスポンシブ対応 */
@media (max-width: 1024px) {
    /* PC版を非表示 */
    .related-grants-card {
        display: none;
    }
    
    /* スマホ版を表示 */
    .mobile-related-grants {
        display: block;
    }
}

@media (max-width: 768px) {
    .mobile-related-grants {
        margin: 30px 0;
        padding: 20px;
        border-radius: 10px;
    }
    
    .mobile-related-grants .section-title {
        font-size: 18px;
    }
    
    .mobile-grant-title {
        font-size: 15px;
    }
}

/* ========================================
   AI ASSISTANT STYLES
   ======================================== */
   PC PERMANENT AI CHAT - サイドバー常時表示
   =============================================== */
.gus-pc-ai-permanent {
    background: #FFFFFF;
    border: 2px solid #E5E5E5;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    height: 800px;
    max-height: calc(100vh - 80px);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.gus-pc-ai-permanent:hover {
    border-color: #000000;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.12);
}

.gus-pc-ai-permanent-header {
    padding: 16px;
    background: linear-gradient(135deg, #000000 0%, #1a1a1a 100%);
    color: #FFFFFF;
    border-bottom: 2px solid #E5E5E5;
    flex-shrink: 0;
}

.gus-pc-ai-permanent-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 800;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}

.gus-pc-ai-permanent-subtitle {
    font-size: 11px;
    opacity: 0.85;
    font-weight: 500;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.gus-pc-ai-permanent-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #FAFAFA;
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

.gus-pc-ai-permanent-messages::-webkit-scrollbar {
    width: 6px;
}

.gus-pc-ai-permanent-messages::-webkit-scrollbar-track {
    background: transparent;
}

.gus-pc-ai-permanent-messages::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.gus-pc-ai-permanent-messages::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.3);
}

.gus-pc-ai-permanent-input-container {
    padding: 14px;
    background: #FFFFFF;
    border-top: 2px solid #E5E5E5;
    flex-shrink: 0;
}

.gus-pc-ai-permanent-input-wrapper {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.gus-pc-ai-permanent-input {
    flex: 1;
    padding: 12px 14px;
    border: 2px solid #E5E5E5;
    border-radius: 10px;
    font-size: 13px;
    font-family: inherit;
    min-height: 44px;
    max-height: 100px;
    resize: none;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: #F8F8F8;
    color: #1A1A1A;
}

.gus-pc-ai-permanent-input:focus {
    outline: none;
    border-color: #000000;
    background: #FFFFFF;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.gus-pc-ai-permanent-input::placeholder {
    color: #999999;
}

.gus-pc-ai-permanent-send {
    width: 44px;
    height: 44px;
    background: #000000;
    color: #FFFFFF;
    border: 2px solid #000000;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.gus-pc-ai-permanent-send:hover:not(:disabled) {
    background: #FFFFFF;
    color: #000000;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.gus-pc-ai-permanent-send:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.gus-pc-ai-permanent-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.gus-pc-ai-permanent-suggestion {
    padding: 7px 12px;
    background: #F8F8F8;
    color: #1A1A1A;
    border: 1px solid #E5E5E5;
    border-radius: 16px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
}

.gus-pc-ai-permanent-suggestion:hover {
    background: #000000;
    color: #FFFFFF;
    border-color: #000000;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Message Bubble - Shared */
.gus-ai-message {
    display: flex;
    gap: 10px;
    max-width: 90%;
    animation: messageSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.gus-ai-message--assistant {
    align-self: flex-start;
}

.gus-ai-message--user {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.gus-ai-message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.gus-ai-message--assistant .gus-ai-message-avatar {
    background: linear-gradient(135deg, #000000 0%, #333333 100%);
    color: #FFFFFF;
    border-color: #000000;
}

.gus-ai-message--user .gus-ai-message-avatar {
    background: linear-gradient(135deg, #FFFFFF 0%, #F5F5F5 100%);
    color: #000000;
    border-color: #E5E5E5;
}

.gus-ai-message-content {
    background: #F8F8F8;
    padding: 11px 14px;
    border-radius: 10px;
    border: 1px solid #E5E5E5;
    font-size: 12px;
    line-height: 1.6;
    color: #1A1A1A;
    word-wrap: break-word;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.gus-ai-message--user .gus-ai-message-content {
    background: #000000;
    color: #FFFFFF;
    border-color: #000000;
}

/* Typing Indicator */
.gus-ai-typing {
    display: flex;
    gap: 10px;
    max-width: 90%;
    align-self: flex-start;
}

.gus-ai-typing-dots {
    background: #F8F8F8;
    padding: 11px 14px;
    border-radius: 10px;
    border: 1px solid #E5E5E5;
    display: flex;
    gap: 4px;
    align-items: center;
}

.gus-ai-typing-dot {
    width: 6px;
    height: 6px;
    background: #666666;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.gus-ai-typing-dot:nth-child(2) {
    animation-delay: 0.2s;
}

.gus-ai-typing-dot:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 80%, 100% { 
        transform: scale(0.7); 
        opacity: 0.4; 
    }
    40% { 
        transform: scale(1); 
        opacity: 1; 
    }
}


</style>

<script>
(function() {
    'use strict';
    
    // 目次自動生成（デスクトップ & モバイル両方）
    function generateTOC() {
        const content = document.querySelector('.column-content');
        const tocNav = document.getElementById('toc-nav');
        const mobileTocList = document.getElementById('mobileTocList');
        
        if (!content) return;
        
        const headings = content.querySelectorAll('h2, h3');
        if (headings.length === 0) {
            if (tocNav) {
                tocNav.innerHTML = '<p style="font-size: 15px; color: #999; padding: 12px 0;">目次がありません</p>';
            }
            if (mobileTocList) {
                mobileTocList.innerHTML = '<p style="font-size: 15px; color: #999; padding: 24px;">目次がありません</p>';
            }
            return;
        }
        
        // デスクトップ用TOC生成
        if (tocNav) {
            let tocHTML = '<ul>';
            headings.forEach((heading, index) => {
                const id = 'heading-' + index;
                heading.id = id;
                
                const level = heading.tagName === 'H2' ? 1 : 2;
                const indent = level === 2 ? 'padding-left: 20px;' : '';
                
                tocHTML += `<li style="${indent}"><a href="#${id}">${heading.textContent}</a></li>`;
            });
            tocHTML += '</ul>';
            tocNav.innerHTML = tocHTML;
        }
        
        // モバイル用TOC生成
        if (mobileTocList) {
            let mobileTocHTML = '<ul>';
            headings.forEach((heading, index) => {
                const id = heading.id || 'heading-' + index;
                heading.id = id;
                
                const level = heading.tagName === 'H2' ? 1 : 2;
                
                mobileTocHTML += `<li data-level="${level}"><a href="#${id}">${heading.textContent}</a></li>`;
            });
            mobileTocHTML += '</ul>';
            mobileTocList.innerHTML = mobileTocHTML;
            
            // モバイルTOCリンククリックでパネルを閉じる
            mobileTocList.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    closeMobilePanel();
                });
            });
        }
    }
    
    // AI送信処理（デスクトップ - PC常時表示）
    function initDesktopAI() {
        const sendBtn = document.getElementById('pcPermanentSend');
        const input = document.getElementById('pcPermanentInput');
        const container = document.getElementById('pcPermanentMessages');
        
        if (!sendBtn || !input || !container) return;
        
        sendBtn.addEventListener('click', function() {
            const question = input.value.trim();
            if (!question) return;
            
            sendAIMessage(question, container, input);
        });
        
        // Enterで送信
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendBtn.click();
            }
        });
        
        // 提案チップクリック
        const suggestions = document.querySelectorAll('.gus-pc-ai-permanent-suggestion');
        suggestions.forEach(function(chip) {
            chip.addEventListener('click', function() {
                const question = this.getAttribute('data-question');
                if (question) {
                    input.value = question;
                    sendBtn.click();
                }
            });
        });
    }
    
    // AI送信処理（モバイル）
    function initMobileAI() {
        const sendBtn = document.getElementById('mobileAiSend');
        const input = document.getElementById('mobileAiInput');
        const container = document.getElementById('mobileAiMessages');
        
        if (!sendBtn || !input || !container) return;
        
        sendBtn.addEventListener('click', function() {
            const question = input.value.trim();
            if (!question) return;
            
            sendAIMessage(question, container, input);
        });
        
        // Enterで送信
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendBtn.click();
            }
        });
    }
    
    // AI共通送信処理（実機能実装）
    function sendAIMessage(question, container, input) {
        // ユーザーメッセージ追加
        const userMsg = document.createElement('div');
        userMsg.className = 'ai-message';
        userMsg.innerHTML = `
            <div class="ai-avatar" style="background: var(--color-accent); color: var(--color-primary);" aria-hidden="true">
                <i class="fas fa-user"></i>
            </div>
            <div class="ai-content" style="background: var(--color-primary); color: var(--color-secondary);">
                ${escapeHtml(question)}
            </div>
        `;
        container.appendChild(userMsg);
        
        // 入力クリア
        input.value = '';
        
        // ローディング表示
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'ai-message ai-message-assistant ai-loading';
        loadingMsg.innerHTML = `
            <div class="ai-avatar" aria-hidden="true">
                <i class="fas fa-robot"></i>
            </div>
            <div class="ai-content">
                <i class="fas fa-spinner fa-spin" aria-hidden="true"></i> 考え中...
            </div>
        `;
        container.appendChild(loadingMsg);
        container.scrollTop = container.scrollHeight;
        
        // AI APIを呼び出し
        callAIAPI(question)
            .then(response => {
                // ローディング削除
                loadingMsg.remove();
                
                // AI応答を追加
                const aiMsg = document.createElement('div');
                aiMsg.className = 'ai-message ai-message-assistant';
                aiMsg.innerHTML = `
                    <div class="ai-avatar" aria-hidden="true">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-content">
                        ${formatAIResponse(response)}
                    </div>
                `;
                container.appendChild(aiMsg);
                container.scrollTop = container.scrollHeight;
            })
            .catch(error => {
                // ローディング削除
                loadingMsg.remove();
                
                // エラー表示
                const errorMsg = document.createElement('div');
                errorMsg.className = 'ai-message ai-message-assistant';
                errorMsg.innerHTML = `
                    <div class="ai-avatar" aria-hidden="true">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="ai-content" style="color: #dc2626;">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i> 
                        申し訳ございません。エラーが発生しました。もう一度お試しください。
                    </div>
                `;
                container.appendChild(errorMsg);
                container.scrollTop = container.scrollHeight;
                
                console.error('[AI Error]', error);
            });
    }
    
    // AI API呼び出し（フォールバック付き実装）
    function callAIAPI(question) {
        // 記事コンテンツを取得
        const content = document.querySelector('.column-content');
        const title = document.querySelector('.column-title');
        const contentText = content ? content.innerText : '';
        const titleText = title ? title.innerText : '';
        
        // Debug: Check if wpApiSettings exists
        console.log('🔍 wpApiSettings:', window.wpApiSettings);
        console.log('🔍 wpApiSettings.nonce:', window.wpApiSettings ? window.wpApiSettings.nonce : 'NOT FOUND');
        
        // Try REST API first
        const apiUrl = window.wpApiSettings ? window.wpApiSettings.root + 'gi-api/v1/ai-chat' : '/wp-json/gi-api/v1/ai-chat';
        const nonce = window.wpApiSettings && window.wpApiSettings.nonce ? window.wpApiSettings.nonce : '';
        
        console.log('🔵 Trying REST API:', apiUrl);
        console.log('🔐 Nonce:', nonce ? 'EXISTS (length: ' + nonce.length + ')' : 'MISSING ❌');
        
        return fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce
            },
            body: JSON.stringify({
                question: question,
                context: {
                    title: titleText,
                    content: contentText.substring(0, 3000),
                    type: 'column'
                }
            })
        })
        .then(response => {
            console.log('📡 REST API Response status:', response.status);
            if (!response.ok) {
                console.warn('[AI REST API] Failed with status:', response.status);
                // Try AJAX fallback
                return callAIAPI_AJAX(question, titleText, contentText);
            }
            return response.json();
        })
        .then(data => {
            if (typeof data === 'string') {
                return data;
            }
            if (data && data.success && data.data && data.data.answer) {
                console.log('✅ AI Response received:', data.data.source);
                return data.data.answer;
            } else if (data && typeof data === 'object' && 'answer' in data) {
                // AJAX response format
                console.log('✅ AI Response received via AJAX');
                return data.answer;
            } else {
                console.warn('[AI API] Invalid response structure, using fallback');
                return generateFallbackResponse(question);
            }
        })
        .catch(error => {
            console.warn('[AI REST API] Request failed, trying AJAX fallback:', error);
            return callAIAPI_AJAX(question, titleText, contentText);
        });
    }
    
    // AJAX Fallback
    function callAIAPI_AJAX(question, titleText, contentText) {
        console.log('🔄 Trying AJAX fallback');
        
        const ajaxUrl = (window.ajaxSettings && window.ajaxSettings.ajaxurl) || window.ajaxurl || '/wp-admin/admin-ajax.php';
        const nonce = window.wpApiSettings && window.wpApiSettings.nonce ? window.wpApiSettings.nonce : '';
        
        console.log('🔍 AJAX URL:', ajaxUrl);
        console.log('🔐 AJAX Nonce:', nonce ? 'EXISTS' : 'MISSING ❌');
        
        const formData = new FormData();
        formData.append('action', 'gi_ai_chat');
        formData.append('nonce', nonce);
        formData.append('question', question);
        formData.append('context', JSON.stringify({
            title: titleText,
            content: contentText.substring(0, 3000),
            type: 'column'
        }));
        
        return fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('📡 AJAX Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('📦 AJAX Response data:', data);
            if (data.success && data.data && data.data.answer) {
                console.log('✅ AJAX Response received:', data.data.source);
                return data.data.answer;
            } else {
                console.warn('[AI AJAX] Invalid response structure:', data);
                console.warn('[AI AJAX] Using fallback response');
                return generateFallbackResponse(question);
            }
        })
        .catch(error => {
            console.warn('[AI AJAX] Request failed, using fallback:', error);
            return generateFallbackResponse(question);
        });
    }
    
    // フォールバックレスポンス生成
    function generateFallbackResponse(question) {
        const lowerQ = question.toLowerCase();
        
        if (lowerQ.includes('期限') || lowerQ.includes('締切') || lowerQ.includes('いつまで')) {
            return 'この記事の「申請期限」または「スケジュール」のセクションをご確認ください。補助金の締切情報が記載されています。';
        }
        if (lowerQ.includes('条件') || lowerQ.includes('要件') || lowerQ.includes('対象')) {
            return 'この記事の「申請条件」または「対象者」のセクションに詳細が記載されています。ご自身の事業が対象となるかご確認ください。';
        }
        if (lowerQ.includes('金額') || lowerQ.includes('補助率') || lowerQ.includes('いくら')) {
            return 'この記事の「補助金額」または「補助率」のセクションをご覧ください。補助金の金額や率について詳しく説明されています。';
        }
        if (lowerQ.includes('申請') || lowerQ.includes('手続き') || lowerQ.includes('方法')) {
            return 'この記事の「申請方法」または「申請手順」のセクションに、申請の流れが詳しく記載されています。ステップごとにご確認ください。';
        }
        if (lowerQ.includes('書類') || lowerQ.includes('必要') || lowerQ.includes('提出')) {
            return 'この記事の「必要書類」または「提出書類」のセクションをご確認ください。申請に必要な書類のリストが記載されています。';
        }
        
        return `ご質問ありがとうございます。「${question}」について、この記事内で詳しく説明されています。\n\n記事の目次から該当するセクションをご確認いただくか、ページ内検索（Ctrl+F / Cmd+F）で関連するキーワードを検索してみてください。\n\nさらに詳しい情報が必要な場合は、関連する助成金ページもご参照ください。`;
    }
    
    // HTML escape
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // AIレスポンスのフォーマット
    function formatAIResponse(text) {
        return escapeHtml(text).replace(/\n/g, '<br>');
    }
    
    // モバイルパネル制御
    function initMobilePanel() {
        const btn = document.getElementById('mobileTocBtn');
        const overlay = document.getElementById('mobileTocOverlay');
        const panel = document.getElementById('mobileTocPanel');
        const closeBtn = document.getElementById('mobileTocClose');
        const tabs = document.querySelectorAll('.gus-mobile-nav-tab');
        
        if (!btn || !overlay || !panel) return;
        
        // パネルを開く
        btn.addEventListener('click', function() {
            overlay.classList.add('active');
            panel.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            panel.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            
            // フォーカスをパネルに移動
            panel.focus();
        });
        
        // パネルを閉じる
        function closePanel() {
            overlay.classList.remove('active');
            panel.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            panel.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            
            // フォーカスをボタンに戻す
            btn.focus();
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closePanel);
        }
        
        overlay.addEventListener('click', closePanel);
        
        // Escapeキーで閉じる
        panel.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePanel();
            }
        });
        
        // グローバルに公開
        window.closeMobilePanel = closePanel;
        
        // タブ切り替え
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // タブのアクティブ状態を切り替え
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');
                
                // コンテンツを切り替え
                const contents = panel.querySelectorAll('.gus-mobile-nav-content');
                contents.forEach(content => {
                    if ((targetTab === 'ai' && content.id === 'aiContent') ||
                        (targetTab === 'toc' && content.id === 'tocContent')) {
                        content.classList.add('active');
                        content.removeAttribute('hidden');
                    } else {
                        content.classList.remove('active');
                        content.setAttribute('hidden', '');
                    }
                });
            });
        });
    }
    
    // 初期化
    function init() {
        generateTOC();
        initDesktopAI();
        initMobileAI();
        initMobilePanel();
        
        console.log('[OK] Single Column v4.0 - SEO Enhanced + Wide Sticky Sidebar initialized');
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
</script>