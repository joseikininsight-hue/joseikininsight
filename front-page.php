<?php
/**
 * Grant Insight Perfect - Front Page Template
 * SEO完全最適化版 - 隙間修正・背景強化
 *
 * @package Grant_Insight_Perfect
 * @version 8.0-seo-ultimate
 */

get_header(); ?>

<style>
/* ============================================
   フロントページ - SEO完全最適化版
   v8.0-seo-ultimate
   ============================================ */

/* CSS変数定義 */
:root {
    --color-bg-primary: #ffffff;
    --color-bg-secondary: #f8f9fa;
    --color-bg-tertiary: #f0f2f5;
    --color-gradient-start: #f5f7fa;
    --color-gradient-end: #e8ecf1;
    --color-border: #e5e5e7;
    --color-overlay: rgba(0, 0, 0, 0.02);
    --header-height: 56px;
    --transition-smooth: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ベース設定 - 隙間完全削除 */
html {
    scroll-behavior: smooth;
    height: 100%;
    overflow-y: auto !important;
    -webkit-text-size-adjust: 100%;
}

body {
    margin: 0;
    padding: 0;
    height: auto;
    min-height: 100vh;
    overflow-x: hidden;
    overflow-y: auto !important;
}

/* メインコンテンツ - 隙間削除 */
.site-main {
    padding: 0;
    margin: 0;
    background: var(--color-bg-primary);
    position: relative;
    width: 100%;
    overflow: visible;
}

/* ヘッダー直下の隙間を完全削除 */
#main-content {
    margin-top: var(--header-height) !important;
    padding-top: 0 !important;
}

.gih-hero-section {
    margin-top: 0 !important;
    padding-top: 80px !important;
}

/* セクション設定 - 隙間削除 */
.front-page-section {
    position: relative;
    margin: 0;
    padding: 0;
    width: 100%;
    display: block;
    overflow: visible;
}

.front-page-section + .front-page-section {
    margin-top: 0;
    border-top: none;
}

/* 背景グラデーション - 繊細な網目パターン */
.site-main::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
        linear-gradient(135deg, 
            var(--color-gradient-start) 0%, 
            var(--color-bg-primary) 25%,
            var(--color-bg-secondary) 50%,
            var(--color-bg-primary) 75%,
            var(--color-gradient-end) 100%
        );
    z-index: -2;
    pointer-events: none;
}

/* 網目パターンオーバーレイ - 繊細なデザイン */
.site-main::after {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        repeating-linear-gradient(
            0deg,
            transparent,
            transparent 19px,
            var(--color-overlay) 19px,
            var(--color-overlay) 20px
        ),
        repeating-linear-gradient(
            90deg,
            transparent,
            transparent 19px,
            var(--color-overlay) 19px,
            var(--color-overlay) 20px
        );
    background-size: 20px 20px;
    opacity: 0.4;
    z-index: -1;
    pointer-events: none;
    animation: subtleMove 60s linear infinite;
}

@keyframes subtleMove {
    0% {
        background-position: 0 0, 0 0;
    }
    100% {
        background-position: 20px 20px, 20px 20px;
    }
}

/* スクロールプログレスバー - SEO対応 */
.scroll-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #333333 0%, #666666 50%, #333333 100%);
    background-size: 200% 100%;
    z-index: 9999;
    transition: width 0.1s ease;
    width: 0%;
    will-change: width;
    animation: progressShimmer 2s ease-in-out infinite;
}

@keyframes progressShimmer {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

/* 広告スペース - 統一デザイン */
.front-ad-space {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
    position: relative;
    z-index: 1;
}

/* セクションアニメーション - デスクトップのみ */
.section-animate {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (min-width: 1024px) {
    .section-animate {
        opacity: 0;
        transform: translateY(30px);
    }
    
    .section-animate.visible {
        opacity: 1;
        transform: translateY(0);
    }
}

/* モバイル最適化 - スクロール問題完全解決 */
@media (max-width: 1023px) {
    html {
        height: 100%;
        overflow-y: auto !important;
    }
    
    body {
        height: auto;
        min-height: 100vh;
        overflow-y: auto !important;
        overflow-x: hidden;
    }
    
    .site-main {
        display: block !important;
        width: 100% !important;
        overflow: visible !important;
        height: auto !important;
        min-height: auto !important;
    }
    
    .front-page-section {
        display: block !important;
        width: 100% !important;
        min-height: auto !important;
        height: auto !important;
        overflow: visible !important;
        position: relative !important;
        opacity: 1 !important;
        transform: none !important;
    }
    
    section {
        display: block !important;
        width: 100% !important;
        overflow: visible !important;
        opacity: 1 !important;
    }
    
    .gih-hero-section {
        padding-top: 60px !important;
    }
}

/* タブレット対応 */
@media (min-width: 768px) and (max-width: 1023px) {
    .gih-hero-section {
        padding-top: 80px !important;
    }
}

/* デスクトップ大画面 */
@media (min-width: 1400px) {
    .front-ad-space {
        max-width: 1400px;
    }
}

/* パフォーマンス最適化 */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
    
    .site-main::after {
        animation: none !important;
    }
    
    .scroll-progress {
        animation: none !important;
    }
}

/* プリント対応 */
@media print {
    .site-main::before,
    .site-main::after,
    .scroll-progress {
        display: none !important;
    }
    
    .front-ad-space {
        display: none !important;
    }
}

/* タッチデバイス最適化 */
@media (hover: none) and (pointer: coarse) {
    .site-main::after {
        opacity: 0.3;
    }
}


/* レスポンシブ画像 */
img {
    max-width: 100%;
    height: auto;
    display: block;
}

/* アクセシビリティ - スキップリンク */
.skip-to-content {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 100000;
}

.skip-to-content:focus {
    top: 0;
}

/* セマンティック要素の最適化 */
main {
    display: block;
}

article,
aside,
section {
    display: block;
}
</style>

<!-- SEO メタタグ - 完全最適化 -->
<meta name="description" content="<?php echo esc_attr(get_bloginfo('description')); ?> | 全国の補助金・助成金情報をAIが効率的に検索。業種別・地域別対応、専門家による申請サポート完備。完全無料。">
<meta name="keywords" content="補助金,助成金,AI検索,事業支援,申請サポート,無料検索,ビジネス支援,事業再構築補助金,ものづくり補助金,IT導入補助金,<?php echo esc_attr(get_bloginfo('name')); ?>">
<meta name="author" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
<link rel="canonical" href="<?php echo esc_url(home_url('/')); ?>">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:title" content="<?php echo esc_attr(get_bloginfo('name')); ?> | 補助金・助成金をAIが効率的に検索">
<meta property="og:description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">
<meta property="og:url" content="<?php echo esc_url(home_url('/')); ?>">
<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>">
<meta property="og:locale" content="ja_JP">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo esc_attr(get_bloginfo('name')); ?> | 補助金・助成金をAIが効率的に検索">
<meta name="twitter:description" content="<?php echo esc_attr(get_bloginfo('description')); ?>">

<!-- スキップリンク - アクセシビリティ -->
<a href="#main-content" class="skip-to-content">メインコンテンツへスキップ</a>

<main id="main" class="site-main" role="main" itemscope itemtype="https://schema.org/WebPage">

    <?php
    /**
     * 1. Hero Section
     * メインビジュアルとキャッチコピー
     */
    ?>
    <section class="front-page-section section-animate" 
             id="hero-section"
             aria-labelledby="hero-heading"
             itemscope 
             itemtype="https://schema.org/WPHeader">
        <?php get_template_part('template-parts/front-page/section', 'hero'); ?>
    </section>

    <?php
    // 広告: ヒーロー下部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-hero-bottom">
            <?php ji_display_ad('front_hero_bottom', 'front-page'); ?>
        </div>
    <?php endif; ?>

    <?php
    /**
     * 2. Column Zone Section
     * コラムゾーン（補助金活用のヒントやノウハウ）
     */
    // 広告: コラムゾーン上部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-column-top">
            <?php ji_display_ad('front_column_zone_top', 'front-page'); ?>
        </div>
    <?php endif; ?>
    
    <section class="front-page-section section-animate" 
             id="column-section"
             aria-labelledby="column-heading"
             itemscope 
             itemtype="https://schema.org/CollectionPage">
        <?php 
        error_log('[Front Page] Loading column zone');
        get_template_part('template-parts/column/zone'); 
        error_log('[Front Page] Column zone loaded');
        ?>
    </section>

    <?php
    // 広告: コラムゾーン下部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-column-bottom">
            <?php ji_display_ad('front_column_zone_bottom', 'front-page'); ?>
        </div>
    <?php endif; ?>

    <?php
    /**
     * 3. Grant News Section
     * 補助金ニュース（締切間近・おすすめ・新着）
     */
    // 広告: 補助金ニュース上部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-grant-news-top">
            <?php ji_display_ad('front_grant_news_top', 'front-page'); ?>
        </div>
    <?php endif; ?>
    
    <section class="front-page-section section-animate" 
             id="grant-news-section"
             aria-labelledby="grant-news-heading"
             itemscope 
             itemtype="https://schema.org/ItemList">
        <?php 
        error_log('[Front Page] Loading grant news');
        
        // 締切間近の補助金を取得（30日以内、最大9件）
        $all_grants_for_deadline = get_posts(array(
            'post_type' => 'grant',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        ));

        $deadline_soon_grants = array();
        $today = strtotime(date('Y-m-d'));
        $deadline_soon_date = strtotime('+30 days');

        foreach ($all_grants_for_deadline as $grant) {
            $deadline_date = get_field('deadline_date', $grant->ID);
            if (empty($deadline_date)) {
                $deadline_date = get_post_meta($grant->ID, 'deadline_date', true);
            }
            if (empty($deadline_date)) {
                $deadline_date = get_post_meta($grant->ID, '_deadline_date', true);
            }
            
            if (!empty($deadline_date)) {
                $deadline_timestamp = strtotime($deadline_date);
                if ($deadline_timestamp >= $today && $deadline_timestamp <= $deadline_soon_date) {
                    $deadline_soon_grants[] = $grant;
                    if (count($deadline_soon_grants) >= 9) break;
                }
            }
        }
        $deadline_soon_grants = is_array($deadline_soon_grants) ? $deadline_soon_grants : array();

        // レコメンド補助金を取得（注目度の高い9件）
        $all_grants_for_featured = get_posts(array(
            'post_type' => 'grant',
            'posts_per_page' => 100,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish'
        ));

        $recommended_grants = array();
        foreach ($all_grants_for_featured as $grant) {
            $is_featured = get_field('is_featured', $grant->ID);
            if (empty($is_featured)) {
                $is_featured = get_post_meta($grant->ID, 'is_featured', true);
            }
            if (empty($is_featured)) {
                $is_featured = get_post_meta($grant->ID, '_is_featured', true);
            }
            
            if ($is_featured == '1' || $is_featured === true || $is_featured === 1) {
                $recommended_grants[] = $grant;
                if (count($recommended_grants) >= 9) break;
            }
        }

        // フォールバック：注目がない場合は最新のものを取得
        if (empty($recommended_grants)) {
            $recommended_grants = array_slice($all_grants_for_featured, 0, 9);
        }
        $recommended_grants = is_array($recommended_grants) ? $recommended_grants : array();

        // 新着補助金を取得（最新9件）
        $new_grants = get_posts(array(
            'post_type' => 'grant',
            'posts_per_page' => 9,
            'orderby' => 'date',
            'order' => 'DESC',
            'post_status' => 'publish',
            'suppress_filters' => false
        ));
        if (!is_array($new_grants)) {
            $new_grants = array();
        }
        if (empty($new_grants)) {
            $new_grants = get_posts(array(
                'post_type' => 'grant',
                'posts_per_page' => 9,
                'orderby' => 'ID',
                'order' => 'DESC',
                'post_status' => 'publish'
            ));
        }
        
        // 変数を明示的に渡す
        set_query_var('deadline_soon_grants', $deadline_soon_grants);
        set_query_var('recommended_grants', $recommended_grants);
        set_query_var('new_grants', $new_grants);
        
        get_template_part('template-parts/front-page/grant-tabs-section'); 
        error_log('[Front Page] Grant news loaded');
        ?>
    </section>

    <?php
    // 広告: 補助金ニュース下部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-grant-news-bottom">
            <?php ji_display_ad('front_grant_news_bottom', 'front-page'); ?>
        </div>
    <?php endif; ?>

    <?php
    /**
     * 4. Grant Zone Section (補助金検索)
     * 補助金検索 + カテゴリ + 都道府県
     */
    // 広告: 検索エリア上部
    if (function_exists('ji_display_ad')): ?>
        <div class="front-ad-space front-ad-search-top">
            <?php ji_display_ad('front_search_top', 'front-page'); ?>
        </div>
    <?php endif; ?>
    
    <section class="front-page-section section-animate" 
             id="grant-zone-section"
             aria-labelledby="grant-zone-heading"
             itemscope 
             itemtype="https://schema.org/SearchAction">
        <?php 
        error_log('[Front Page] Loading grant zone');
        get_template_part('template-parts/front-page/section', 'search'); 
        error_log('[Front Page] Grant zone loaded');
        ?>
    </section>

</main>

<!-- スクロールプログレスバー -->
<div class="scroll-progress" 
     id="scroll-progress" 
     role="progressbar" 
     aria-label="ページスクロール進捗"
     aria-valuemin="0" 
     aria-valuemax="100" 
     aria-valuenow="0"></div>

<!-- 構造化データ - WebSite -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "<?php echo esc_js(get_bloginfo('name')); ?>",
    "url": "<?php echo esc_url(home_url('/')); ?>",
    "description": "<?php echo esc_js(get_bloginfo('description')); ?>",
    "inLanguage": "ja",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "<?php echo esc_url(home_url('/grants/')); ?>?search={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    },
    "publisher": {
        "@type": "Organization",
        "name": "<?php echo esc_js(get_bloginfo('name')); ?>",
        "url": "<?php echo esc_url(home_url('/')); ?>"
    }
}
</script>

<!-- 構造化データ - BreadcrumbList -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "ホーム",
            "item": "<?php echo esc_url(home_url('/')); ?>"
        }
    ]
}
</script>

<script>
/**
 * フロントページ JavaScript - SEO完全最適化版
 * v8.0-seo-ultimate
 */
(function() {
    'use strict';
    
    // 初期化チェック
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        setupScrollProgress();
        setupSectionAnimations();
        setupSmoothScroll();
        setupPerformanceMonitoring();
        setupSEOEnhancements();
        
        console.log('[✓] Front Page SEO Ultimate v8.0 - Initialized');
    }
    
    /**
     * スクロールプログレスバー
     */
    function setupScrollProgress() {
        const progressBar = document.getElementById('scroll-progress');
        if (!progressBar) return;
        
        let ticking = false;
        
        function updateProgressBar() {
            const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrolled = window.scrollY;
            const progress = scrollHeight > 0 ? (scrolled / scrollHeight) * 100 : 0;
            
            progressBar.style.width = Math.min(Math.max(progress, 0), 100) + '%';
            progressBar.setAttribute('aria-valuenow', Math.round(progress));
            
            ticking = false;
        }
        
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateProgressBar);
                ticking = true;
            }
        }, { passive: true });
        
        // 初期表示
        window.requestAnimationFrame(updateProgressBar);
    }
    
    /**
     * セクションアニメーション（デスクトップのみ）
     */
    function setupSectionAnimations() {
        if (window.innerWidth < 1024) {
            // モバイルでは即座に表示
            document.querySelectorAll('.section-animate').forEach(section => {
                section.classList.add('visible');
                section.style.opacity = '1';
                section.style.transform = 'none';
            });
            return;
        }
        
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    sectionObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.section-animate').forEach(section => {
            sectionObserver.observe(section);
        });
    }
    
    /**
     * スムーススクロール
     */
    function setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href !== '#' && href !== '#0') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        const headerOffset = 80;
                        const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerOffset;
                        
                        window.scrollTo({
                            top: targetPosition,
                            behavior: 'smooth'
                        });
                        
                        // フォーカス移動（アクセシビリティ）
                        target.setAttribute('tabindex', '-1');
                        target.focus();
                    }
                }
            });
        });
    }
    
    /**
     * パフォーマンスモニタリング
     */
    function setupPerformanceMonitoring() {
        if (!('performance' in window)) return;
        
        window.addEventListener('load', function() {
            setTimeout(function() {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData) {
                    const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
                    const domContentLoaded = perfData.domContentLoadedEventEnd - perfData.domContentLoadedEventStart;
                    
                    console.log('[Performance] Page Load:', loadTime + 'ms');
                    console.log('[Performance] DOM Content Loaded:', domContentLoaded + 'ms');
                    
                    // Google Analytics 4
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'page_timing', {
                            'event_category': 'Performance',
                            'event_label': 'Front Page',
                            'value': Math.round(loadTime)
                        });
                    }
                }
            }, 0);
        });
    }
    
    /**
     * SEO拡張機能
     */
    function setupSEOEnhancements() {
        // ページ表示時間追跡
        const startTime = performance.now();
        
        window.addEventListener('beforeunload', function() {
            const timeOnPage = Math.round(performance.now() - startTime);
            
            if (typeof gtag !== 'undefined') {
                gtag('event', 'time_on_page', {
                    'event_category': 'Engagement',
                    'event_label': 'Front Page',
                    'value': timeOnPage
                });
            }
        });
        
        // スクロール深度追跡
        let maxScrollDepth = 0;
        let milestones = [25, 50, 75, 100];
        let trackedMilestones = new Set();
        
        function trackScrollDepth() {
            const scrollPercentage = Math.round(
                (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100
            );
            
            if (scrollPercentage > maxScrollDepth) {
                maxScrollDepth = scrollPercentage;
                
                milestones.forEach(milestone => {
                    if (scrollPercentage >= milestone && !trackedMilestones.has(milestone)) {
                        trackedMilestones.add(milestone);
                        
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'scroll_depth', {
                                'event_category': 'Engagement',
                                'event_label': milestone + '%',
                                'value': milestone
                            });
                        }
                        
                        console.log('[SEO] Scroll milestone:', milestone + '%');
                    }
                });
            }
        }
        
        let scrollTicking = false;
        window.addEventListener('scroll', function() {
            if (!scrollTicking) {
                window.requestAnimationFrame(function() {
                    trackScrollDepth();
                    scrollTicking = false;
                });
                scrollTicking = true;
            }
        }, { passive: true });
        
        // ビューポート可視性追跡
        if ('IntersectionObserver' in window) {
            const sectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const sectionId = entry.target.id;
                        
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'section_view', {
                                'event_category': 'Engagement',
                                'event_label': sectionId,
                                'value': 1
                            });
                        }
                        
                        console.log('[SEO] Section viewed:', sectionId);
                    }
                });
            }, {
                threshold: 0.5
            });
            
            document.querySelectorAll('.front-page-section').forEach(section => {
                if (section.id) {
                    sectionObserver.observe(section);
                }
            });
        }
        
        console.log('[SEO] SEO enhancements enabled');
    }
    
})();
</script>

<?php get_footer(); ?>
