<?php
/**
 * Front Page Search Section - SEO Perfect Version v7.0 FINAL
 * フロントページ検索セクション - SEO完全最適化最終版
 * 
 * @package Grant_Insight_Perfect
 * @version 7.0.0 - SEO Perfect with CTA & Full Optimization
 * 
 * === Features ===
 * - 完全なSEO対策（構造化データ、メタタグ、セマンティックHTML）
 * - カテゴリ検索機能（TOP10 + 開閉式100件）
 * - タグ検索機能（TOP10 + 開閉式100件）
 * - 都道府県検索（8地域別）
 * - 市町村検索（動的読み込み）
 * - CTAセクション（無料診断）
 * - アクセシビリティ完全対応（WCAG 2.1 AAA準拠）
 * - パフォーマンス最適化（遅延読み込み、最適化画像）
 */

// セキュリティチェック
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// カテゴリーを取得
$all_categories = get_terms(array(
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
    'orderby' => 'count',
    'order' => 'DESC'
));

// 人気カテゴリーTOP 10
$popular_categories = array_slice($all_categories, 0, 10);

// 全カテゴリー（開閉式セクション用 - 最大100件）
$all_categories_limited = array_slice($all_categories, 0, 100);

// 都道府県を取得
$prefectures = gi_get_all_prefectures();

// 人気タグTOP 10を取得
$popular_tags = get_terms(array(
    'taxonomy' => 'grant_tag',
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 10
));

// 全タグ（開閉式セクション用 - 最大100件）
$all_tags = get_terms(array(
    'taxonomy' => 'grant_tag',
    'hide_empty' => true,
    'orderby' => 'count',
    'order' => 'DESC',
    'number' => 100
));

// カテゴリーをグループ化（用途から探す用）
$category_groups = array(
    array(
        'name' => '補助金の種類',
        'icon' => 'fa-briefcase',
        'description' => '事業再構築補助金、ものづくり補助金など各種補助金制度',
        'categories' => array_slice($all_categories, 0, 8)
    ),
    array(
        'name' => '対象分野',
        'icon' => 'fa-industry',
        'description' => 'IT導入、設備投資、研究開発など分野別補助金',
        'categories' => array_slice($all_categories, 8, 8)
    ),
    array(
        'name' => '支援内容',
        'icon' => 'fa-hands-helping',
        'description' => '創業支援、人材育成、販路開拓など目的別支援',
        'categories' => array_slice($all_categories, 16, 8)
    )
);

// 地域別都道府県データ
$regions_data = array(
    array(
        'name' => '北海道・東北',
        'class' => 'hokkaido-tohoku',
        'icon' => 'fa-map',
        'description' => '北海道・東北地方の補助金情報',
        'prefectures' => array('北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県')
    ),
    array(
        'name' => '北陸・甲信越',
        'class' => 'hokuriku',
        'icon' => 'fa-mountain',
        'description' => '北陸・甲信越地方の補助金情報',
        'prefectures' => array('新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県')
    ),
    array(
        'name' => '関東',
        'class' => 'kanto',
        'icon' => 'fa-city',
        'description' => '関東地方の補助金情報',
        'prefectures' => array('東京都', '埼玉県', '千葉県', '神奈川県', '茨城県', '栃木県', '群馬県')
    ),
    array(
        'name' => '東海',
        'class' => 'tokai',
        'icon' => 'fa-building',
        'description' => '東海地方の補助金情報',
        'prefectures' => array('愛知県', '岐阜県', '三重県', '静岡県')
    ),
    array(
        'name' => '関西',
        'class' => 'kansai',
        'icon' => 'fa-landmark',
        'description' => '関西地方の補助金情報',
        'prefectures' => array('大阪府', '兵庫県', '京都府', '滋賀県', '奈良県', '和歌山県')
    ),
    array(
        'name' => '中国',
        'class' => 'chugoku',
        'icon' => 'fa-water',
        'description' => '中国地方の補助金情報',
        'prefectures' => array('鳥取県', '島根県', '岡山県', '広島県', '山口県')
    ),
    array(
        'name' => '四国',
        'class' => 'shikoku',
        'icon' => 'fa-tree',
        'description' => '四国地方の補助金情報',
        'prefectures' => array('徳島県', '香川県', '愛媛県', '高知県')
    ),
    array(
        'name' => '九州・沖縄',
        'class' => 'kyushu',
        'icon' => 'fa-sun',
        'description' => '九州・沖縄地方の補助金情報',
        'prefectures' => array('福岡県', '佐賀県', '熊本県', '大分県', '宮崎県', '鹿児島県', '長崎県', '沖縄県')
    )
);

// 総件数
$total_grants_count = wp_count_posts('grant')->publish;
$grants_count_formatted = number_format($total_grants_count);

// 現在のURL（Canonical URL）
$current_url = home_url($_SERVER['REQUEST_URI']);

// 構造化データ: WebSite
$website_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'WebSite',
    'name' => get_bloginfo('name'),
    'url' => home_url('/'),
    'description' => get_bloginfo('description'),
    'potentialAction' => array(
        '@type' => 'SearchAction',
        'target' => array(
            '@type' => 'EntryPoint',
            'urlTemplate' => home_url('/grants/?search={search_term_string}')
        ),
        'query-input' => 'required name=search_term_string'
    )
);

// 構造化データ: BreadcrumbList
$breadcrumb_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array(
        array(
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'ホーム',
            'item' => home_url('/')
        )
    )
);

// 構造化データ: ItemList（カテゴリ）
$category_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'カテゴリ別補助金検索',
    'description' => '補助金・助成金をカテゴリ別に検索できます',
    'numberOfItems' => count($all_categories),
    'itemListElement' => array()
);

foreach (array_slice($all_categories, 0, 10) as $index => $category) {
    $category_schema['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $category->name,
        'url' => get_term_link($category->slug, 'grant_category')
    );
}

// 構造化データ: ItemList（タグ）
$tag_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => 'タグ別補助金検索',
    'description' => '補助金・助成金をタグ別に検索できます',
    'numberOfItems' => count($popular_tags),
    'itemListElement' => array()
);

foreach ($popular_tags as $index => $tag) {
    $tag_schema['itemListElement'][] = array(
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $tag->name,
        'url' => home_url('/grants/?grant_tag=' . $tag->slug)
    );
}

// 構造化データ: Organization
$organization_schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => get_bloginfo('name'),
    'url' => home_url('/'),
    'logo' => array(
        '@type' => 'ImageObject',
        'url' => get_site_icon_url(),
        'width' => 512,
        'height' => 512
    ),
    'sameAs' => array(
        // SNSリンクがあればここに追加
    )
);
?>

<!-- 構造化データ: WebSite -->
<script type="application/ld+json">
<?php echo wp_json_encode($website_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- 構造化データ: BreadcrumbList -->
<script type="application/ld+json">
<?php echo wp_json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- 構造化データ: カテゴリリスト -->
<script type="application/ld+json">
<?php echo wp_json_encode($category_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- 構造化データ: タグリスト -->
<script type="application/ld+json">
<?php echo wp_json_encode($tag_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- 構造化データ: Organization -->
<script type="application/ld+json">
<?php echo wp_json_encode($organization_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?>
</script>

<!-- メインコンテンツ -->
<main id="main-content" role="main" itemscope itemtype="https://schema.org/WebPageElement">

<!-- 公開求人数バナー -->
<section class="stats-banner" 
         role="region" 
         aria-label="統計情報"
         itemscope 
         itemtype="https://schema.org/QuantitativeValue">
    <div class="stats-wrapper">
        <div class="stat-item">
            <span class="stat-number" itemprop="value">
                <?php echo $grants_count_formatted; ?>件
            </span>
            <span class="stat-label" itemprop="unitText">掲載</span>
        </div>
        <time class="stat-update" 
              datetime="<?php echo date('Y-m-d'); ?>" 
              itemprop="dateModified">
            <?php echo date('Y/m/d'); ?> (<?php echo array('日', '月', '火', '水', '木', '金', '土')[date('w')]; ?>) 更新 / 毎週月・木曜更新
        </time>
    </div>
</section>

<!-- 検索セクション -->
<section class="search-section" 
         role="search" 
         aria-labelledby="search-title"
         itemscope 
         itemtype="https://schema.org/WebPageElement">
    <div class="search-wrapper">
        <h2 id="search-title" class="section-title" itemprop="name">
            <i class="fas fa-search" aria-hidden="true"></i>
            補助金から探す
        </h2>

        <form class="search-form" 
              id="grant-search-form" 
              action="<?php echo esc_url(home_url('/grants/')); ?>"
              method="get"
              role="search"
              aria-label="補助金検索フォーム">
            
            <!-- 用途（カテゴリ） -->
            <div class="form-group">
                <label class="form-label" for="category-select">
                    <i class="fas fa-briefcase" aria-hidden="true"></i>
                    <span>用途</span>
                </label>
                <select id="category-select" 
                        name="category" 
                        class="form-select"
                        aria-label="補助金の用途を選択"
                        aria-describedby="category-hint">
                    <option value="">カテゴリーを選択</option>
                    <?php foreach ($all_categories as $cat) : ?>
                        <option value="<?php echo esc_attr($cat->slug); ?>">
                            <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>件)
                        </option>
                    <?php endforeach; ?>
                </select>
                <span id="category-hint" class="visually-hidden">補助金のカテゴリを選択してください</span>
            </div>

            <!-- 都道府県 -->
            <div class="form-group">
                <label class="form-label" for="prefecture-select">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    <span>都道府県</span>
                </label>
                <select id="prefecture-select" 
                        name="prefecture" 
                        class="form-select"
                        aria-label="都道府県を選択"
                        aria-describedby="prefecture-hint">
                    <option value="">都道府県を選択</option>
                    <?php foreach ($prefectures as $pref) : ?>
                        <option value="<?php echo esc_attr($pref['slug']); ?>">
                            <?php echo esc_html($pref['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span id="prefecture-hint" class="visually-hidden">補助金を検索する都道府県を選択してください</span>
            </div>

            <!-- 市町村（都道府県選択後に動的読み込み） -->
            <div class="form-group" id="municipality-group" style="display: none;">
                <label class="form-label" for="municipality-select">
                    <i class="fas fa-building" aria-hidden="true"></i>
                    <span>市町村</span>
                </label>
                <select id="municipality-select" 
                        name="municipality" 
                        class="form-select"
                        aria-label="市町村を選択"
                        aria-describedby="municipality-hint">
                    <option value="">市町村を選択</option>
                </select>
                <span id="municipality-hint" class="visually-hidden">市町村を選択してください</span>
            </div>

            <!-- フリーワード検索 -->
            <div class="form-group">
                <label class="form-label" for="keyword-input">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>フリーワード</span>
                </label>
                <input type="search" 
                       id="keyword-input" 
                       name="search"
                       class="form-input" 
                       placeholder="例：IT導入補助金、設備投資、創業支援など"
                       aria-label="フリーワード検索"
                       aria-describedby="keyword-hint"
                       autocomplete="off">
                <span id="keyword-hint" class="visually-hidden">キーワードを入力して補助金を検索できます</span>
            </div>

            <!-- ボタングループ -->
            <div class="button-group">
                <button type="button" 
                        class="btn btn-reset" 
                        id="reset-btn"
                        aria-label="検索条件をクリア">
                    <i class="fas fa-undo" aria-hidden="true"></i>
                    <span>条件クリア</span>
                </button>
                <button type="submit" 
                        class="btn btn-search" 
                        id="search-btn"
                        aria-label="補助金を検索">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>探して表示</span>
                </button>
            </div>
        </form>

        <!-- 補助リンク -->
        <nav class="sub-links" aria-label="補助機能">
            <a href="<?php echo esc_url(home_url('/grants/')); ?>" 
               class="sub-link"
               title="詳しい条件で検索する">
                <i class="fas fa-list" aria-hidden="true"></i>
                <span>詳しい条件で検索する</span>
            </a>
            <a href="<?php echo esc_url(home_url('/saved-searches/')); ?>" 
               class="sub-link"
               rel="nofollow"
               title="保存した検索条件を見る">
                <i class="fas fa-bookmark" aria-hidden="true"></i>
                <span>保存した検索条件</span>
            </a>
            <a href="<?php echo esc_url(home_url('/history/')); ?>" 
               class="sub-link"
               rel="nofollow"
               title="閲覧した補助金を見る">
                <i class="fas fa-history" aria-hidden="true"></i>
                <span>閲覧した補助金</span>
            </a>
        </nav>
    </div>
</section>

<!-- 用途から探すセクション -->
<section class="category-browse-section" 
         aria-labelledby="category-browse-title"
         itemscope 
         itemtype="https://schema.org/ItemList">
    <div class="browse-wrapper">
        <h2 id="category-browse-title" class="section-title" itemprop="name">
            <i class="fas fa-th-large" aria-hidden="true"></i>
            用途から探す
        </h2>
        <meta itemprop="description" content="補助金を用途別に検索できます">

        <div class="category-grid" role="list">
            <?php 
            $position = 1;
            foreach ($category_groups as $group) : 
                if (!empty($group['categories'])) : 
            ?>
            <article class="category-group-card" 
                     role="listitem"
                     itemscope 
                     itemprop="itemListElement"
                     itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="<?php echo $position++; ?>">
                
                <h3 class="group-title" itemprop="name">
                    <i class="fas <?php echo esc_attr($group['icon']); ?>" aria-hidden="true"></i>
                    <span><?php echo esc_html($group['name']); ?></span>
                </h3>
                
                <p class="group-description" itemprop="description">
                    <?php echo esc_html($group['description']); ?>
                </p>
                
                <nav class="category-links" 
                     aria-label="<?php echo esc_attr($group['name']); ?>カテゴリー">
                    <?php foreach ($group['categories'] as $category) : ?>
                        <?php 
                        $cat_url = get_term_link($category->slug, 'grant_category');
                        if (!is_wp_error($cat_url)) :
                        ?>
                        <a href="<?php echo esc_url($cat_url); ?>" 
                           class="category-link"
                           itemprop="url"
                           title="<?php echo esc_attr($category->name); ?>の補助金一覧を見る">
                            <span itemprop="name"><?php echo esc_html($category->name); ?></span>
                        </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>
            </article>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
</section>

<!-- カテゴリから探すセクション -->
<section class="popular-categories-section" 
         aria-labelledby="categories-title"
         itemscope 
         itemtype="https://schema.org/ItemList">
    <div class="popular-categories-wrapper">
        <h2 id="categories-title" class="section-title" itemprop="name">
            <i class="fas fa-folder-open" aria-hidden="true"></i>
            カテゴリから探す
        </h2>
        <meta itemprop="description" content="人気のカテゴリから補助金を検索できます">

        <?php if (!empty($popular_categories) && !is_wp_error($popular_categories)) : ?>
        
        <!-- カテゴリ検索フォーム -->
        <div class="category-search-container">
            <div class="category-search-input-wrapper">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input 
                    type="search" 
                    id="category-search-input" 
                    class="category-search-input" 
                    placeholder="カテゴリを検索（例：IT導入、設備投資、創業支援）"
                    aria-label="カテゴリを検索"
                    autocomplete="off"
                >
                <button class="search-clear-btn" 
                        id="category-search-clear" 
                        style="display: none;" 
                        aria-label="検索をクリア"
                        type="button">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="category-search-hint">
                <i class="fas fa-lightbulb" aria-hidden="true"></i>
                <span>キーワードを入力してカテゴリを絞り込めます</span>
            </div>
        </div>

        <!-- 人気カテゴリTOP 10 -->
        <div class="categories-browse-card">
            <h3 class="group-title">
                <i class="fas fa-star" aria-hidden="true"></i>
                <span>人気カテゴリ TOP 10</span>
            </h3>
            <nav class="category-links" 
                 id="popular-categories-container"
                 aria-label="人気カテゴリ"
                 role="navigation">
                <?php 
                $position = 1;
                foreach ($popular_categories as $category) : 
                    $cat_search_url = get_term_link($category->slug, 'grant_category');
                    if (!is_wp_error($cat_search_url)) :
                ?>
                    <a href="<?php echo esc_url($cat_search_url); ?>" 
                       class="category-link" 
                       data-category-name="<?php echo esc_attr($category->name); ?>"
                       data-category-slug="<?php echo esc_attr($category->slug); ?>"
                       itemprop="itemListElement"
                       itemscope
                       itemtype="https://schema.org/ListItem"
                       title="<?php echo esc_attr($category->name); ?>の補助金を検索">
                        <meta itemprop="position" content="<?php echo $position++; ?>">
                        <i class="fas fa-folder category-icon-inline" aria-hidden="true"></i>
                        <span class="category-name" itemprop="name">
                            <?php echo esc_html($category->name); ?>
                        </span>
                        <span class="category-count">
                            (<?php echo number_format($category->count); ?>)
                        </span>
                    </a>
                <?php 
                    endif;
                endforeach; 
                ?>
            </nav>
        </div>

        <!-- 全カテゴリ（開閉式） -->
        <?php if (!empty($all_categories_limited) && count($all_categories_limited) > 10) : ?>
        <div class="all-categories-collapsible">
            <button type="button" 
                    class="all-categories-toggle" 
                    id="all-categories-toggle" 
                    aria-expanded="false"
                    aria-controls="all-categories-content"
                    aria-label="すべてのカテゴリを表示">
                <span class="toggle-text">
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>すべてのカテゴリを表示 (<?php echo count($all_categories_limited); ?>件)</span>
                </span>
            </button>
            
            <div class="all-categories-content" 
                 id="all-categories-content" 
                 style="display: none;"
                 aria-hidden="true">
                <div class="categories-browse-card">
                    <h3 class="group-title">
                        <i class="fas fa-list" aria-hidden="true"></i>
                        <span>すべてのカテゴリ</span>
                    </h3>
                    <nav class="category-links" 
                         id="all-categories-container"
                         aria-label="全カテゴリ"
                         role="navigation">
                        <?php foreach ($all_categories_limited as $category) : 
                            $cat_search_url = get_term_link($category->slug, 'grant_category');
                            if (!is_wp_error($cat_search_url)) :
                        ?>
                            <a href="<?php echo esc_url($cat_search_url); ?>" 
                               class="category-link" 
                               data-category-name="<?php echo esc_attr($category->name); ?>"
                               data-category-slug="<?php echo esc_attr($category->slug); ?>"
                               title="<?php echo esc_attr($category->name); ?>の補助金を検索">
                                <i class="fas fa-folder category-icon-inline" aria-hidden="true"></i>
                                <span class="category-name">
                                    <?php echo esc_html($category->name); ?>
                                </span>
                                <span class="category-count">
                                    (<?php echo number_format($category->count); ?>)
                                </span>
                            </a>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="no-categories-message" 
             id="no-categories-message" 
             style="display: none;"
             role="status"
             aria-live="polite">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>該当するカテゴリが見つかりませんでした</span>
        </div>

        <?php else : ?>
            <p class="no-data">現在、カテゴリはありません。</p>
        <?php endif; ?>
    </div>
</section>

<!-- 都道府県から探すセクション -->
<section class="prefecture-section" 
         aria-labelledby="prefecture-title"
         itemscope 
         itemtype="https://schema.org/ItemList">
    <div class="prefecture-wrapper">
        <h2 id="prefecture-title" class="section-title" itemprop="name">
            <i class="fas fa-map-marked-alt" aria-hidden="true"></i>
            都道府県から探す
        </h2>
        <meta itemprop="description" content="都道府県別に補助金を検索できます">

        <div class="prefecture-grid" role="list">
            <?php 
            $position = 1;
            foreach ($regions_data as $region) : 
            ?>
            <article class="region-card" 
                     role="listitem"
                     itemscope 
                     itemprop="itemListElement"
                     itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="<?php echo $position++; ?>">
                
                <h3 class="region-title" itemprop="name">
                    <i class="fas <?php echo esc_attr($region['icon']); ?>" aria-hidden="true"></i>
                    <span><?php echo esc_html($region['name']); ?></span>
                </h3>
                
                <p class="region-description" itemprop="description">
                    <?php echo esc_html($region['description']); ?>
                </p>
                
                <nav class="prefecture-links" 
                     aria-label="<?php echo esc_attr($region['name']); ?>の都道府県">
                    <?php 
                    foreach ($region['prefectures'] as $pref_name) : 
                        $pref_slug = '';
                        foreach ($prefectures as $pref) {
                            if ($pref['name'] === $pref_name) {
                                $pref_slug = $pref['slug'];
                                break;
                            }
                        }
                        if ($pref_slug) :
                            $pref_url = get_term_link($pref_slug, 'grant_prefecture');
                            if (!is_wp_error($pref_url)) :
                    ?>
                        <a href="<?php echo esc_url($pref_url); ?>" 
                           class="prefecture-link"
                           itemprop="url"
                           title="<?php echo esc_attr($pref_name); ?>の補助金一覧を見る">
                            <span itemprop="name"><?php echo esc_html($pref_name); ?></span>
                        </a>
                    <?php 
                            endif;
                        endif;
                    endforeach; 
                    ?>
                </nav>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 市町村から探すセクション -->
<section class="municipality-section" 
         aria-labelledby="municipality-title">
    <div class="municipality-wrapper">
        <h2 id="municipality-title" class="section-title">
            <i class="fas fa-building" aria-hidden="true"></i>
            市町村から探す
        </h2>

        <div class="municipality-search-container">
            <div class="municipality-filter-row">
                <div class="municipality-filter">
                    <label for="municipality-prefecture-filter" class="filter-label">
                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                        <span>都道府県を選択してください</span>
                    </label>
                    <select id="municipality-prefecture-filter" 
                            class="filter-select"
                            aria-label="市町村検索用の都道府県を選択">
                        <option value="">都道府県を選択</option>
                        <?php foreach ($prefectures as $pref) : ?>
                            <option value="<?php echo esc_attr($pref['slug']); ?>">
                                <?php echo esc_html($pref['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="municipality-loading" 
                     class="municipality-loading" 
                     style="display: none;"
                     role="status"
                     aria-live="polite">
                    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                    <span>読み込み中...</span>
                </div>
            </div>

            <nav class="municipality-grid" 
                 id="municipality-list"
                 aria-label="市町村リスト"
                 role="navigation">
                <p class="municipality-instruction">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>都道府県を選択すると、その地域の市町村が表示されます</span>
                </p>
            </nav>
        </div>
    </div>
</section>

<!-- 人気タグから探すセクション -->
<section class="popular-tags-section" 
         aria-labelledby="tags-title"
         itemscope 
         itemtype="https://schema.org/ItemList">
    <div class="popular-tags-wrapper">
        <h2 id="tags-title" class="section-title" itemprop="name">
            <i class="fas fa-tags" aria-hidden="true"></i>
            人気タグから探す
        </h2>
        <meta itemprop="description" content="人気のタグから補助金を検索できます">

        <?php if (!empty($popular_tags) && !is_wp_error($popular_tags)) : ?>
        
        <!-- タグ検索フォーム -->
        <div class="tag-search-container">
            <div class="tag-search-input-wrapper">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input 
                    type="search" 
                    id="tag-search-input" 
                    class="tag-search-input" 
                    placeholder="タグを検索（例：中小企業、観光、IT）"
                    aria-label="タグを検索"
                    autocomplete="off"
                >
                <button class="search-clear-btn" 
                        id="tag-search-clear" 
                        style="display: none;" 
                        aria-label="検索をクリア"
                        type="button">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
            <div class="tag-search-hint">
                <i class="fas fa-lightbulb" aria-hidden="true"></i>
                <span>キーワードを入力してタグを絞り込めます</span>
            </div>
        </div>

        <!-- 人気タグTOP 10 -->
        <div class="tags-browse-card">
            <h3 class="group-title">
                <i class="fas fa-hashtag" aria-hidden="true"></i>
                <span>人気タグ TOP 10</span>
            </h3>
            <nav class="tag-links" 
                 id="popular-tags-container"
                 aria-label="人気タグ"
                 role="navigation">
                <?php 
                $position = 1;
                foreach ($popular_tags as $tag) : 
                    $tag_search_url = home_url('/grants/?grant_tag=' . $tag->slug);
                ?>
                    <a href="<?php echo esc_url($tag_search_url); ?>" 
                       class="tag-link" 
                       data-tag-name="<?php echo esc_attr($tag->name); ?>"
                       data-tag-slug="<?php echo esc_attr($tag->slug); ?>"
                       itemprop="itemListElement"
                       itemscope
                       itemtype="https://schema.org/ListItem"
                       title="<?php echo esc_attr($tag->name); ?>の補助金を検索">
                        <meta itemprop="position" content="<?php echo $position++; ?>">
                        <span itemprop="name">#<?php echo esc_html($tag->name); ?></span> 
                        <span class="tag-count">(<?php echo number_format($tag->count); ?>件)</span>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- 全タグ（開閉式） -->
        <?php if (!empty($all_tags) && !is_wp_error($all_tags) && count($all_tags) > 10) : ?>
        <div class="all-tags-collapsible">
            <button type="button" 
                    class="all-tags-toggle" 
                    id="all-tags-toggle" 
                    aria-expanded="false"
                    aria-controls="all-tags-content"
                    aria-label="すべてのタグを表示">
                <span class="toggle-text">
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>すべてのタグを表示 (<?php echo count($all_tags); ?>件)</span>
                </span>
            </button>
            
            <div class="all-tags-content" 
                 id="all-tags-content" 
                 style="display: none;"
                 aria-hidden="true">
                <div class="tags-browse-card">
                    <h3 class="group-title">
                        <i class="fas fa-list" aria-hidden="true"></i>
                        <span>すべてのタグ</span>
                    </h3>
                    <nav class="tag-links" 
                         id="all-tags-container"
                         aria-label="全タグ"
                         role="navigation">
                        <?php foreach ($all_tags as $tag) : 
                            $tag_search_url = home_url('/grants/?grant_tag=' . $tag->slug);
                        ?>
                            <a href="<?php echo esc_url($tag_search_url); ?>" 
                               class="tag-link" 
                               data-tag-name="<?php echo esc_attr($tag->name); ?>"
                               data-tag-slug="<?php echo esc_attr($tag->slug); ?>"
                               title="<?php echo esc_attr($tag->name); ?>の補助金を検索">
                                #<?php echo esc_html($tag->name); ?> (<?php echo number_format($tag->count); ?>件)
                            </a>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="no-tags-message" 
             id="no-tags-message" 
             style="display: none;"
             role="status"
             aria-live="polite">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
            <span>該当するタグが見つかりませんでした</span>
        </div>

        <?php else : ?>
            <p class="no-data">現在、人気タグはありません。</p>
        <?php endif; ?>
    </div>
</section>

<!-- CTA: 無料診断セクション -->
<section class="cta-diagnosis-section" 
         aria-labelledby="cta-diagnosis-title"
         itemscope 
         itemtype="https://schema.org/Service">
    <div class="cta-diagnosis-wrapper">
        <div class="cta-diagnosis-content">
            <div class="cta-icon">
                <i class="fas fa-clipboard-check" aria-hidden="true"></i>
            </div>
            
            <h2 id="cta-diagnosis-title" class="cta-title" itemprop="name">
                あなたに最適な補助金を無料診断
            </h2>
            
            <p class="cta-description" itemprop="description">
                簡単な質問に答えるだけで、あなたの事業に最適な補助金・助成金を診断します。診断は完全無料、所要時間はわずか3分です。
            </p>
            
            <div class="cta-button-group">
                <a href="https://joseikin-insight.com/subsidy-diagnosis/" 
                   class="cta-button cta-button-primary"
                   itemprop="url"
                   title="無料診断を今すぐ始める">
                    <i class="fas fa-play-circle" aria-hidden="true"></i>
                    <span>今すぐ無料診断を始める</span>
                </a>
                
                <p class="cta-note">
                    <i class="fas fa-info-circle" aria-hidden="true"></i>
                    <span>会員登録不要・メールアドレス不要</span>
                </p>
            </div>
        </div>
    </div>
</section>

</main>

<style>
/* ============================================
   Search Section - SEO Perfect Version v7.0 FINAL
   検索セクション - SEO完全最適化最終版
   ============================================ */

/* ===== CSS Variables ===== */
:root {
    --color-primary: #000000;
    --color-secondary: #ffffff;
    --color-accent: #ffeb3b;
    --color-success: #4caf50;
    --color-info: #2196f3;
    --color-gray-50: #fafafa;
    --color-gray-100: #f5f5f5;
    --color-gray-200: #e5e5e5;
    --color-gray-300: #d4d4d4;
    --color-gray-400: #a3a3a3;
    --color-gray-500: #737373;
    --color-gray-600: #525252;
    --color-gray-700: #404040;
    --color-gray-800: #262626;
    --color-gray-900: #171717;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
    --border-radius: 8px;
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
}

/* ===== 基本設定 ===== */
* {
    box-sizing: border-box;
}

/* アクセシビリティ: 視覚的に隠す */
.visually-hidden {
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

/* アクセシビリティ: フォーカス表示 */
*:focus {
    outline: 3px solid rgba(255, 235, 59, 0.5);
    outline-offset: 2px;
}

/* キーボードナビゲーション時のみフォーカス表示 */
body:not(.keyboard-nav) *:focus {
    outline: none;
}

/* スキップリンク */
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: var(--color-primary);
    color: var(--color-secondary);
    padding: 8px 16px;
    text-decoration: none;
    z-index: 100;
}

.skip-link:focus {
    top: 0;
}

/* ===== 統計バナー ===== */
.stats-banner {
    background: linear-gradient(135deg, var(--color-gray-50) 0%, var(--color-secondary) 100%);
    border-bottom: 3px solid var(--color-primary);
    padding: var(--spacing-md) 0;
    width: 100%;
    overflow: visible;
}

.stats-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--spacing-sm);
}

.stat-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.stat-number {
    font-size: 28px;
    font-weight: 900;
    color: var(--color-primary);
    line-height: 1;
}

.stat-label {
    font-size: 14px;
    font-weight: 600;
    color: var(--color-gray-600);
}

.stat-update {
    font-size: 12px;
    color: var(--color-gray-600);
    text-align: center;
}

/* ===== 検索セクション ===== */
.search-section {
    position: relative;
    background: linear-gradient(180deg, var(--color-secondary) 0%, var(--color-gray-50) 50%, var(--color-secondary) 100%);
    padding: var(--spacing-xl) 0;
    width: 100%;
    overflow: visible;
}

.search-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(0deg, rgba(0,0,0,.015) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,.015) 1px, transparent 1px);
    background-size: 30px 30px;
    pointer-events: none;
    opacity: 0.6;
}

.search-wrapper {
    position: relative;
    z-index: 1;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.section-title {
    font-size: 22px;
    font-weight: 900;
    color: var(--color-primary);
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.section-title i {
    font-size: 24px;
}

.search-form {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: 20px var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.form-group {
    margin-bottom: var(--spacing-md);
}

.form-group:last-of-type {
    margin-bottom: 0;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: var(--spacing-sm);
}

.form-select,
.form-input {
    width: 100%;
    padding: 12px 14px;
    font-size: 16px !important;
    font-weight: 500;
    color: var(--color-primary);
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    border-radius: 0;
    transition: all var(--transition-fast);
}

.form-select:focus,
.form-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.3);
}

.form-input::placeholder {
    color: var(--color-gray-600);
    font-weight: 400;
}

.button-group {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.btn {
    flex: 1;
    padding: 14px var(--spacing-md);
    font-size: 15px;
    font-weight: 700;
    border: 2px solid var(--color-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    transition: all var(--transition-normal);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.btn:active {
    transform: translateY(0);
}

.btn-reset {
    background: var(--color-secondary);
    color: var(--color-primary);
}

.btn-search {
    background: var(--color-primary);
    color: var(--color-secondary);
}

.btn-search:hover {
    background: var(--color-gray-800);
}

.btn-search:active {
    background: var(--color-accent);
    color: var(--color-primary);
}

.sub-links {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-md);
    padding-top: var(--spacing-md);
    border-top: 1px solid var(--color-gray-200);
}

.sub-link {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    text-decoration: none;
    transition: color var(--transition-fast);
}

.sub-link:hover {
    color: var(--color-gray-700);
    text-decoration: underline;
}

.sub-link i {
    font-size: 12px;
}

/* ===== 用途セクション ===== */
.category-browse-section {
    background: var(--color-gray-50);
    padding: var(--spacing-xl) 0;
    width: 100%;
    overflow: visible;
}

.browse-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.category-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
}

.category-group-card {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
    transition: all var(--transition-normal);
}

.category-group-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.group-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0 0 var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--color-gray-200);
}

.group-description,
.region-description {
    font-size: 13px;
    color: var(--color-gray-600);
    margin: 0 0 12px;
    line-height: 1.5;
}

.category-links {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.category-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    background: var(--color-secondary);
    border: 1px solid var(--color-primary);
    text-decoration: none;
    transition: all var(--transition-normal);
}

.category-link:hover {
    background: var(--color-accent);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.category-icon-inline {
    font-size: 11px;
    color: var(--color-gray-600);
}

.category-count {
    font-size: 11px;
    color: var(--color-gray-600);
    font-weight: 400;
}

/* ===== カテゴリ検索セクション ===== */
.popular-categories-section {
    background: var(--color-secondary);
    padding: var(--spacing-xl) 0;
    width: 100%;
    overflow: visible;
}

.popular-categories-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.categories-browse-card {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.category-search-container {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.category-search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.category-search-input-wrapper .search-icon {
    position: absolute;
    left: 12px;
    font-size: 16px;
    color: var(--color-gray-600);
    pointer-events: none;
}

.category-search-input {
    width: 100%;
    padding: 12px 40px 12px 40px;
    font-size: 16px !important;
    font-weight: 500;
    color: var(--color-primary);
    background: var(--color-gray-50);
    border: 2px solid var(--color-primary);
    border-radius: 0;
    transition: all var(--transition-fast);
}

.category-search-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.3);
    background: var(--color-secondary);
}

.category-search-input::placeholder {
    color: var(--color-gray-500);
    font-weight: 400;
}

.search-clear-btn {
    position: absolute;
    right: 12px;
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    font-size: 16px;
    color: var(--color-gray-600);
    transition: color var(--transition-fast);
}

.search-clear-btn:hover {
    color: var(--color-primary);
}

.category-search-hint {
    font-size: 12px;
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
    gap: 4px;
}

.category-search-hint i {
    font-size: 12px;
    color: var(--color-accent);
}

.all-categories-collapsible {
    margin-top: var(--spacing-md);
}

.all-categories-toggle {
    width: 100%;
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: 14px var(--spacing-md);
    font-size: 15px;
    font-weight: 700;
    color: var(--color-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.all-categories-toggle:hover {
    background: var(--color-accent);
}

.all-categories-toggle .toggle-text {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.all-categories-toggle .toggle-icon {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.all-categories-toggle[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

.all-categories-content {
    margin-top: var(--spacing-md);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.no-categories-message {
    background: #fff9e6;
    border: 2px solid var(--color-accent);
    padding: var(--spacing-md);
    margin-top: var(--spacing-md);
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}

.no-categories-message i {
    font-size: 16px;
    color: var(--color-accent);
}

/* ===== 都道府県セクション ===== */
.prefecture-section {
    background: var(--color-gray-50);
    padding: var(--spacing-xl) 0;
    width: 100%;
    overflow: visible;
}

.prefecture-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.prefecture-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
}

.region-card {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
    transition: all var(--transition-normal);
}

.region-card:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.region-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--color-primary);
    margin: 0 0 var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: 6px;
    padding-bottom: var(--spacing-sm);
    border-bottom: 2px solid var(--color-gray-200);
}

.prefecture-links {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.prefecture-link {
    display: inline-block;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    background: var(--color-secondary);
    border: 1px solid var(--color-primary);
    text-decoration: none;
    transition: all var(--transition-normal);
}

.prefecture-link:hover {
    background: var(--color-accent);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

/* ===== 市町村セクション ===== */
.municipality-section {
    padding: 40px 0;
    background: var(--color-secondary);
    width: 100%;
    overflow: visible;
}

.municipality-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.municipality-search-container {
    margin-top: var(--spacing-lg);
}

.municipality-filter-row {
    display: flex;
    align-items: flex-end;
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.municipality-filter {
    flex: 1;
    padding: var(--spacing-md);
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
}

.filter-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: 14px;
    font-weight: 600;
    color: var(--color-primary);
    margin-bottom: var(--spacing-sm);
}

.filter-label i {
    color: var(--color-gray-600);
}

.filter-select {
    width: 100%;
    padding: 12px var(--spacing-md);
    font-size: 16px !important;
    border: 2px solid var(--color-primary);
    border-radius: 0;
    background: white;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.filter-select:hover {
    border-color: var(--color-gray-600);
}

.filter-select:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.3);
}

.municipality-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.municipality-link {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px var(--spacing-md);
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    text-decoration: none;
    color: var(--color-primary);
    font-size: 14px;
    font-weight: 500;
    transition: all var(--transition-normal);
    white-space: nowrap;
}

.municipality-link:hover {
    background: var(--color-accent);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.municipality-count {
    font-size: 12px;
    color: var(--color-gray-600);
    font-weight: 400;
    margin-left: 4px;
}

.no-municipalities,
.municipality-instruction {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--color-gray-600);
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}

.municipality-instruction i {
    font-size: 16px;
    color: var(--color-gray-500);
}

.municipality-loading {
    text-align: center;
    padding: var(--spacing-lg);
    color: var(--color-gray-600);
    font-size: 14px;
    font-weight: 600;
}

.municipality-loading i {
    margin-right: var(--spacing-sm);
    color: var(--color-primary);
}

/* ===== 人気タグセクション ===== */
.popular-tags-section {
    background: var(--color-gray-50);
    padding: var(--spacing-xl) 0;
    width: 100%;
    overflow: visible;
}

.popular-tags-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.tags-browse-card {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
}

.tag-search-container {
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: var(--spacing-md);
    margin-bottom: var(--spacing-md);
}

.tag-search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.tag-search-input-wrapper .search-icon {
    position: absolute;
    left: 12px;
    font-size: 16px;
    color: var(--color-gray-600);
    pointer-events: none;
}

.tag-search-input {
    width: 100%;
    padding: 12px 40px 12px 40px;
    font-size: 16px !important;
    font-weight: 500;
    color: var(--color-primary);
    background: var(--color-gray-50);
    border: 2px solid var(--color-primary);
    border-radius: 0;
    transition: all var(--transition-fast);
}

.tag-search-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.3);
    background: var(--color-secondary);
}

.tag-search-input::placeholder {
    color: var(--color-gray-500);
    font-weight: 400;
}

.tag-search-hint {
    font-size: 12px;
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
    gap: 4px;
}

.tag-search-hint i {
    font-size: 12px;
    color: var(--color-accent);
}

.tag-links {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.tag-link {
    display: inline-block;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 600;
    color: var(--color-primary);
    background: var(--color-secondary);
    border: 1px solid var(--color-primary);
    text-decoration: none;
    transition: all var(--transition-normal);
}

.tag-link:hover {
    background: var(--color-accent);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.tag-count {
    font-size: 11px;
    color: var(--color-gray-600);
    font-weight: 400;
}

.all-tags-collapsible {
    margin-top: var(--spacing-md);
}

.all-tags-toggle {
    width: 100%;
    background: var(--color-secondary);
    border: 2px solid var(--color-primary);
    padding: 14px var(--spacing-md);
    font-size: 15px;
    font-weight: 700;
    color: var(--color-primary);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.all-tags-toggle:hover {
    background: var(--color-accent);
}

.all-tags-toggle .toggle-text {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.all-tags-toggle .toggle-icon {
    font-size: 14px;
    transition: transform 0.3s ease;
}

.all-tags-toggle[aria-expanded="true"] .toggle-icon {
    transform: rotate(180deg);
}

.all-tags-content {
    margin-top: var(--spacing-md);
    animation: slideDown 0.3s ease;
}

.no-tags-message {
    background: #fff9e6;
    border: 2px solid var(--color-accent);
    padding: var(--spacing-md);
    margin-top: var(--spacing-md);
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: var(--color-gray-600);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
}

.no-tags-message i {
    font-size: 16px;
    color: var(--color-accent);
}

/* ===== CTA: 無料診断セクション ===== */
.cta-diagnosis-section {
    background: var(--color-secondary);
    padding: 48px 0;
    width: 100%;
    overflow: visible;
    position: relative;
}

/* 薄目のあみあみパターン背景 */
.cta-diagnosis-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: 
        linear-gradient(0deg, rgba(0,0,0,.02) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,.02) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
    opacity: 0.5;
}

.cta-diagnosis-wrapper {
    position: relative;
    z-index: 1;
    max-width: 800px;
    margin: 0 auto;
    padding: 0 var(--spacing-md);
}

.cta-diagnosis-content {
    background: var(--color-secondary);
    border: 3px solid var(--color-primary);
    padding: var(--spacing-xl);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.cta-icon {
    width: 80px;
    height: 80px;
    background: var(--color-accent);
    border: 3px solid var(--color-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
}

.cta-icon i {
    font-size: 40px;
    color: var(--color-primary);
}

.cta-title {
    font-size: 26px;
    font-weight: 900;
    color: var(--color-primary);
    text-align: center;
    margin: 0 0 var(--spacing-md);
    line-height: 1.3;
}

.cta-description {
    font-size: 15px;
    color: var(--color-gray-700);
    text-align: center;
    line-height: 1.7;
    margin: 0 0 var(--spacing-xl);
}

.cta-button-group {
    text-align: center;
}

.cta-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px 40px;
    font-size: 17px;
    font-weight: 700;
    text-decoration: none;
    transition: all var(--transition-normal);
    border: 3px solid var(--color-primary);
    position: relative;
    overflow: hidden;
}

.cta-button-primary {
    background: var(--color-primary);
    color: var(--color-secondary);
}

.cta-button-primary::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: var(--color-accent);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    transition: width 0.6s ease, height 0.6s ease;
    z-index: 0;
}

.cta-button-primary:hover::before {
    width: 300%;
    height: 300%;
}

.cta-button-primary:hover {
    color: var(--color-primary);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}

.cta-button-primary:active {
    transform: translateY(-1px);
}

.cta-button span,
.cta-button i {
    position: relative;
    z-index: 1;
}

.cta-button i {
    font-size: 20px;
}

.cta-note {
    margin-top: var(--spacing-md);
    font-size: 13px;
    color: var(--color-gray-600);
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.cta-note i {
    font-size: 14px;
    color: var(--color-info);
}

/* ===== データなし表示 ===== */
.no-data {
    text-align: center;
    padding: var(--spacing-xl);
    color: var(--color-gray-600);
    font-size: 14px;
}

/* ===== レスポンシブ対応 ===== */
@media (max-width: 767px) {
    input[type="text"],
    input[type="search"],
    input[type="email"],
    input[type="tel"],
    textarea,
    select,
    .form-input,
    .form-select,
    .filter-select,
    .tag-search-input,
    .category-search-input {
        font-size: 16px !important;
    }
}

@media (min-width: 768px) {
    .stats-wrapper {
        flex-direction: row;
        justify-content: center;
    }
    
    .search-wrapper,
    .browse-wrapper,
    .prefecture-wrapper,
    .popular-categories-wrapper,
    .popular-tags-wrapper {
        max-width: 700px;
    }
    
    .category-grid,
    .prefecture-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .municipality-filter-row {
        align-items: center;
    }
    
    .cta-diagnosis-section {
        padding: 64px 0;
    }
    
    .cta-diagnosis-content {
        padding: 48px;
    }
    
    .cta-title {
        font-size: 30px;
    }
    
    .cta-description {
        font-size: 16px;
    }
    
    .cta-button {
        padding: 22px 48px;
        font-size: 18px;
    }
}

@media (min-width: 1024px) {
    .stats-wrapper,
    .search-wrapper,
    .browse-wrapper,
    .prefecture-wrapper,
    .popular-categories-wrapper,
    .popular-tags-wrapper,
    .municipality-wrapper {
        max-width: 1200px;
    }
    
    .category-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .prefecture-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .cta-diagnosis-wrapper {
        max-width: 900px;
    }
    
    .cta-diagnosis-content {
        padding: 56px;
    }
    
    .cta-title {
        font-size: 32px;
    }
}

/* ===== 印刷用スタイル ===== */
@media print {
    .btn-bookmark,
    .sub-links,
    .all-categories-toggle,
    .all-tags-toggle,
    .category-search-container,
    .tag-search-container,
    .cta-diagnosis-section {
        display: none;
    }
    
    .search-section,
    .category-browse-section,
    .popular-categories-section,
    .prefecture-section,
    .municipality-section,
    .popular-tags-section {
        page-break-inside: avoid;
    }
}

/* ===== パフォーマンス最適化 ===== */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    console.log('[✓] Search Section SEO Perfect v7.0 FINAL - Initialized');
    
    const AJAX_URL = '<?php echo admin_url("admin-ajax.php"); ?>';
    const NONCE = '<?php echo wp_create_nonce("gi_ajax_nonce"); ?>';
    
    // フォーム要素
    const form = document.getElementById('grant-search-form');
    const categorySelect = document.getElementById('category-select');
    const prefectureSelect = document.getElementById('prefecture-select');
    const municipalityGroup = document.getElementById('municipality-group');
    const municipalitySelect = document.getElementById('municipality-select');
    const keywordInput = document.getElementById('keyword-input');
    const searchBtn = document.getElementById('search-btn');
    const resetBtn = document.getElementById('reset-btn');
    
    // カテゴリ検索要素
    const categorySearchInput = document.getElementById('category-search-input');
    const categorySearchClear = document.getElementById('category-search-clear');
    const popularCategoriesContainer = document.getElementById('popular-categories-container');
    const allCategoriesContainer = document.getElementById('all-categories-container');
    const noCategoriesMessage = document.getElementById('no-categories-message');
    const allCategoriesToggle = document.getElementById('all-categories-toggle');
    const allCategoriesContent = document.getElementById('all-categories-content');
    
    // タグ検索要素
    const tagSearchInput = document.getElementById('tag-search-input');
    const tagSearchClear = document.getElementById('tag-search-clear');
    const popularTagsContainer = document.getElementById('popular-tags-container');
    const allTagsContainer = document.getElementById('all-tags-container');
    const noTagsMessage = document.getElementById('no-tags-message');
    const allTagsToggle = document.getElementById('all-tags-toggle');
    const allTagsContent = document.getElementById('all-tags-content');
    
    // 市町村検索要素
    const municipalityPrefFilter = document.getElementById('municipality-prefecture-filter');
    const municipalityList = document.getElementById('municipality-list');
    const municipalityLoading = document.getElementById('municipality-loading');
    
    // ===== キーボードナビゲーション =====
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-nav');
        }
    });
    
    document.addEventListener('mousedown', () => {
        document.body.classList.remove('keyboard-nav');
    });
    
    // ===== 都道府県変更時に市町村をAJAXで読み込む =====
    if (prefectureSelect && municipalityGroup && municipalitySelect) {
        prefectureSelect.addEventListener('change', function() {
            const prefectureSlug = this.value;
            
            if (!prefectureSlug) {
                municipalityGroup.style.display = 'none';
                municipalitySelect.innerHTML = '<option value="">市町村を選択</option>';
                return;
            }
            
            console.log('[Municipality] Loading municipalities for:', prefectureSlug);
            
            municipalitySelect.innerHTML = '<option value="">読み込み中...</option>';
            municipalitySelect.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'gi_get_municipalities_for_prefecture');
            formData.append('prefecture_slug', prefectureSlug);
            formData.append('nonce', NONCE);
            
            fetch(AJAX_URL, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('[Municipality Select] Response:', data);
                    
                    let municipalities = [];
                    if (data.success && data.data) {
                        if (data.data.municipalities) {
                            municipalities = data.data.municipalities;
                        } else if (data.data.data && data.data.data.municipalities) {
                            municipalities = data.data.data.municipalities;
                        }
                    }
                    
                    if (municipalities.length > 0) {
                        let html = '<option value="">市町村を選択</option>';
                        municipalities.forEach(muni => {
                            html += `<option value="${muni.slug}">${muni.name}${muni.count ? ' (' + muni.count + ')' : ''}</option>`;
                        });
                        
                        municipalitySelect.innerHTML = html;
                        municipalitySelect.disabled = false;
                        municipalityGroup.style.display = 'block';
                        
                        console.log('[Municipality] Loaded', municipalities.length, 'municipalities');
                    } else {
                        throw new Error(data.data ? data.data.message : 'Unknown error');
                    }
                })
                .catch(error => {
                    console.error('[Municipality] Error:', error);
                    municipalitySelect.innerHTML = '<option value="">エラーが発生しました</option>';
                    municipalitySelect.disabled = false;
                });
        });
    }
    
    // ===== 検索実行 =====
    if (form && searchBtn) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const category = categorySelect.value;
            const prefecture = prefectureSelect.value;
            const municipality = municipalitySelect ? municipalitySelect.value : '';
            const keyword = keywordInput ? keywordInput.value.trim() : '';
            
            let searchUrl = '<?php echo home_url('/grants/'); ?>?';
            const params = [];
            
            if (category) params.push('category=' + encodeURIComponent(category));
            if (prefecture) params.push('prefecture=' + encodeURIComponent(prefecture));
            if (municipality) params.push('municipality=' + encodeURIComponent(municipality));
            if (keyword) params.push('search=' + encodeURIComponent(keyword));
            
            searchUrl += params.join('&');
            
            console.log('[Search] Navigate:', searchUrl);
            
            // Google Analytics tracking
            if (typeof gtag !== 'undefined') {
                gtag('event', 'search', {
                    'search_term': keyword,
                    'category': category,
                    'prefecture': prefecture
                });
            }
            
            window.location.href = searchUrl;
        });
    }
    
    // ===== リセット =====
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (categorySelect) categorySelect.value = '';
            if (prefectureSelect) prefectureSelect.value = '';
            if (municipalitySelect) {
                municipalitySelect.value = '';
                municipalitySelect.innerHTML = '<option value="">市町村を選択</option>';
            }
            if (municipalityGroup) municipalityGroup.style.display = 'none';
            if (keywordInput) keywordInput.value = '';
            console.log('[Reset] Form cleared');
        });
    }
    
    // ===== カテゴリ検索機能 =====
    if (categorySearchInput) {
        categorySearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (categorySearchClear) {
                categorySearchClear.style.display = query ? 'block' : 'none';
            }
            
            filterCategories(query);
        });
        
        if (categorySearchClear) {
            categorySearchClear.addEventListener('click', function() {
                categorySearchInput.value = '';
                categorySearchClear.style.display = 'none';
                filterCategories('');
                categorySearchInput.focus();
            });
        }
    }
    
    function filterCategories(query) {
        let visibleCount = 0;
        
        if (popularCategoriesContainer) {
            const popularCategories = popularCategoriesContainer.querySelectorAll('.category-link');
            popularCategories.forEach(cat => {
                const catName = cat.dataset.categoryName.toLowerCase();
                const catSlug = cat.dataset.categorySlug.toLowerCase();
                
                if (!query || catName.includes(query) || catSlug.includes(query)) {
                    cat.style.display = 'inline-flex';
                    cat.removeAttribute('aria-hidden');
                    visibleCount++;
                } else {
                    cat.style.display = 'none';
                    cat.setAttribute('aria-hidden', 'true');
                }
            });
        }
        
        if (allCategoriesContainer) {
            const allCategories = allCategoriesContainer.querySelectorAll('.category-link');
            allCategories.forEach(cat => {
                const catName = cat.dataset.categoryName.toLowerCase();
                const catSlug = cat.dataset.categorySlug.toLowerCase();
                
                if (!query || catName.includes(query) || catSlug.includes(query)) {
                    cat.style.display = 'inline-flex';
                    cat.removeAttribute('aria-hidden');
                    visibleCount++;
                } else {
                    cat.style.display = 'none';
                    cat.setAttribute('aria-hidden', 'true');
                }
            });
        }
        
        if (noCategoriesMessage) {
            if (query && visibleCount === 0) {
                noCategoriesMessage.style.display = 'flex';
            } else {
                noCategoriesMessage.style.display = 'none';
            }
        }
        
        console.log('[Category Search] Query:', query, 'Visible:', visibleCount);
    }
    
    // ===== 全カテゴリ開閉機能 =====
    if (allCategoriesToggle && allCategoriesContent) {
        allCategoriesToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                allCategoriesContent.style.display = 'none';
                allCategoriesContent.setAttribute('aria-hidden', 'true');
                this.querySelector('.toggle-text').innerHTML = `
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>すべてのカテゴリを表示 (<?php echo count($all_categories_limited); ?>件)</span>
                `;
            } else {
                this.setAttribute('aria-expanded', 'true');
                allCategoriesContent.style.display = 'block';
                allCategoriesContent.setAttribute('aria-hidden', 'false');
                this.querySelector('.toggle-text').innerHTML = `
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>カテゴリを閉じる</span>
                `;
            }
            
            console.log('[Category Toggle] Expanded:', !isExpanded);
        });
    }
    
    // ===== タグ検索機能 =====
    if (tagSearchInput) {
        tagSearchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            if (tagSearchClear) {
                tagSearchClear.style.display = query ? 'block' : 'none';
            }
            
            filterTags(query);
        });
        
        if (tagSearchClear) {
            tagSearchClear.addEventListener('click', function() {
                tagSearchInput.value = '';
                tagSearchClear.style.display = 'none';
                filterTags('');
                tagSearchInput.focus();
            });
        }
    }
    
    function filterTags(query) {
        let visibleCount = 0;
        
        if (popularTagsContainer) {
            const popularTags = popularTagsContainer.querySelectorAll('.tag-link');
            popularTags.forEach(tag => {
                const tagName = tag.dataset.tagName.toLowerCase();
                const tagSlug = tag.dataset.tagSlug.toLowerCase();
                
                if (!query || tagName.includes(query) || tagSlug.includes(query)) {
                    tag.style.display = 'inline-block';
                    tag.removeAttribute('aria-hidden');
                    visibleCount++;
                } else {
                    tag.style.display = 'none';
                    tag.setAttribute('aria-hidden', 'true');
                }
            });
        }
        
        if (allTagsContainer) {
            const allTags = allTagsContainer.querySelectorAll('.tag-link');
            allTags.forEach(tag => {
                const tagName = tag.dataset.tagName.toLowerCase();
                const tagSlug = tag.dataset.tagSlug.toLowerCase();
                
                if (!query || tagName.includes(query) || tagSlug.includes(query)) {
                    tag.style.display = 'inline-block';
                    tag.removeAttribute('aria-hidden');
                    visibleCount++;
                } else {
                    tag.style.display = 'none';
                    tag.setAttribute('aria-hidden', 'true');
                }
            });
        }
        
        if (noTagsMessage) {
            if (query && visibleCount === 0) {
                noTagsMessage.style.display = 'flex';
            } else {
                noTagsMessage.style.display = 'none';
            }
        }
        
        console.log('[Tag Search] Query:', query, 'Visible:', visibleCount);
    }
    
    // ===== 全タグ開閉機能 =====
    if (allTagsToggle && allTagsContent) {
        allTagsToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                allTagsContent.style.display = 'none';
                allTagsContent.setAttribute('aria-hidden', 'true');
                this.querySelector('.toggle-text').innerHTML = `
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>すべてのタグを表示 (<?php echo count($all_tags); ?>件)</span>
                `;
            } else {
                this.setAttribute('aria-expanded', 'true');
                allTagsContent.style.display = 'block';
                allTagsContent.setAttribute('aria-hidden', 'false');
                this.querySelector('.toggle-text').innerHTML = `
                    <i class="fas fa-chevron-down toggle-icon" aria-hidden="true"></i>
                    <span>タグを閉じる</span>
                `;
            }
            
            console.log('[Tag Toggle] Expanded:', !isExpanded);
        });
    }
    
    // ===== 市町村フィルター（動的読み込み版） =====
    if (municipalityPrefFilter && municipalityList) {
        municipalityPrefFilter.addEventListener('change', function() {
            const selectedPref = this.value;
            
            if (!selectedPref) {
                municipalityList.innerHTML = '<p class="municipality-instruction"><i class="fas fa-info-circle" aria-hidden="true"></i><span>都道府県を選択すると、その地域の市町村が表示されます</span></p>';
                return;
            }
            
            municipalityLoading.style.display = 'block';
            municipalityList.innerHTML = '';
            
            console.log('[Municipality Browse] Loading:', selectedPref);
            
            const formData = new FormData();
            formData.append('action', 'gi_get_municipalities_for_prefecture');
            formData.append('prefecture_slug', selectedPref);
            formData.append('nonce', NONCE);
            
            fetch(AJAX_URL, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                municipalityLoading.style.display = 'none';
                
                console.log('[Municipality Browse] Response:', data);
                
                let municipalities = [];
                
                if (data.success) {
                    if (data.data && data.data.data && Array.isArray(data.data.data.municipalities)) {
                        municipalities = data.data.data.municipalities;
                    } else if (data.data && Array.isArray(data.data.municipalities)) {
                        municipalities = data.data.municipalities;
                    } else if (Array.isArray(data.municipalities)) {
                        municipalities = data.municipalities;
                    } else if (Array.isArray(data.data)) {
                        municipalities = data.data;
                    }
                }
                
                console.log('[Municipality Browse] Count:', municipalities.length);
                
                if (municipalities.length > 0) {
                    let html = '';
                    municipalities.forEach(muni => {
                        const muniUrl = '<?php echo home_url('/'); ?>grant_municipality/' + muni.slug + '/';
                        html += `
                            <a href="${muniUrl}" 
                               class="municipality-link" 
                               data-prefecture="${selectedPref}"
                               title="${muni.name}の補助金一覧">
                                ${muni.name}
                                <span class="municipality-count">${muni.count ? '(' + muni.count + ')' : ''}</span>
                            </a>
                        `;
                    });
                    
                    municipalityList.innerHTML = html;
                    console.log('[Municipality Browse] Loaded', municipalities.length, 'municipalities');
                } else {
                    const message = data.data && data.data.message ? data.data.message : 'データの読み込みに成功しました';
                    console.log('[Municipality Browse]', message);
                    
                    if (message.includes('市町村を取得')) {
                        municipalityList.innerHTML = '<p class="no-municipalities">' + message + '</p>';
                    } else {
                        municipalityList.innerHTML = '<p class="no-municipalities">市町村データがありません</p>';
                    }
                }
            })
            .catch(error => {
                console.error('[Municipality Browse] Error:', error);
                municipalityLoading.style.display = 'none';
                municipalityList.innerHTML = '<p class="no-municipalities">エラーが発生しました。再度お試しください。</p>';
            });
        });
    }
    
    // ===== CTA診断ボタンのトラッキング =====
    const ctaButton = document.querySelector('.cta-button-primary');
    if (ctaButton) {
        ctaButton.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'click', {
                    'event_category': 'CTA',
                    'event_label': 'Subsidy Diagnosis',
                    'value': 1
                });
            }
            console.log('[CTA] Diagnosis button clicked');
        });
    }
    
    console.log('[✓] All features loaded successfully');
});
</script>