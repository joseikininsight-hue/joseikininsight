<?php
/**
 * Affiliate Stats Template - Enhanced Version
 * アフィリエイト広告統計テンプレート（詳細版）
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_stats = $wpdb->prefix . 'ji_affiliate_stats';
$table_stats_detail = $wpdb->prefix . 'ji_affiliate_stats_detail';
$table_ads = $wpdb->prefix . 'ji_affiliate_ads';

// フィルター取得
$period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'last_30_days';
$ad_filter = isset($_GET['ad_filter']) ? intval($_GET['ad_filter']) : 0;
$position_filter = isset($_GET['position_filter']) ? sanitize_text_field($_GET['position_filter']) : '';
$category_filter = isset($_GET['category_filter']) ? intval($_GET['category_filter']) : 0;

// 期間設定
$date_condition = '';
switch ($period) {
    case 'today':
        $date_condition = "DATE(sd.created_at) = CURDATE()";
        $period_label = '今日';
        break;
    case 'yesterday':
        $date_condition = "DATE(sd.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        $period_label = '昨日';
        break;
    case 'last_7_days':
        $date_condition = "sd.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $period_label = '過去7日間';
        break;
    case 'last_30_days':
    default:
        $date_condition = "sd.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $period_label = '過去30日間';
        break;
    case 'this_month':
        $date_condition = "YEAR(sd.created_at) = YEAR(CURDATE()) AND MONTH(sd.created_at) = MONTH(CURDATE())";
        $period_label = '今月';
        break;
    case 'last_month':
        $date_condition = "YEAR(sd.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(sd.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        $period_label = '先月';
        break;
}

// 広告フィルター
$ad_condition = $ad_filter > 0 ? $wpdb->prepare("AND sd.ad_id = %d", $ad_filter) : '';

// 位置フィルター
$position_condition = !empty($position_filter) ? $wpdb->prepare("AND sd.position = %s", $position_filter) : '';

// カテゴリーフィルター
$category_condition = $category_filter > 0 ? $wpdb->prepare("AND sd.category_id = %d", $category_filter) : '';

// 全広告リスト取得（フィルター用）
$all_ads = $wpdb->get_results("SELECT id, title FROM {$table_ads} ORDER BY title ASC");

// 全ポジションリスト取得（フィルター用）
$all_positions = $wpdb->get_col("SELECT DISTINCT position FROM {$table_stats_detail} WHERE position IS NOT NULL AND position != '' ORDER BY position ASC");

// 全カテゴリー取得（フィルター用）
$all_categories = get_categories(array('hide_empty' => false));

// ==================== サマリー統計 ====================
$summary = $wpdb->get_row("
    SELECT 
        COUNT(CASE WHEN event_type = 'impression' THEN 1 END) as total_impressions,
        COUNT(CASE WHEN event_type = 'click' THEN 1 END) as total_clicks,
        COUNT(DISTINCT ad_id) as total_ads,
        COUNT(DISTINCT position) as total_positions,
        COUNT(DISTINCT post_id) as total_pages,
        CASE 
            WHEN COUNT(CASE WHEN event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN event_type = 'click' THEN 1 END) / COUNT(CASE WHEN event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as overall_ctr
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
");

// ==================== 広告別統計 ====================
$stats_by_ad = $wpdb->get_results("
    SELECT 
        sd.ad_id,
        a.title as ad_title,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks,
        CASE 
            WHEN COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) / COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as ctr
    FROM {$table_stats_detail} sd
    INNER JOIN {$table_ads} a ON sd.ad_id = a.id
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    GROUP BY sd.ad_id, a.title
    ORDER BY clicks DESC, impressions DESC
");

// ==================== ポジション別統計 ====================
$stats_by_position = $wpdb->get_results("
    SELECT 
        sd.position,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks,
        CASE 
            WHEN COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) / COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as ctr
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    AND sd.position IS NOT NULL
    AND sd.position != ''
    GROUP BY sd.position
    ORDER BY clicks DESC, impressions DESC
");

// ==================== カテゴリー別統計 ====================
$stats_by_category = $wpdb->get_results("
    SELECT 
        sd.category_id,
        sd.category_name,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks,
        CASE 
            WHEN COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) / COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as ctr
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    AND sd.category_id IS NOT NULL
    AND sd.category_id > 0
    GROUP BY sd.category_id, sd.category_name
    ORDER BY clicks DESC, impressions DESC
");

// ==================== 日別統計（グラフ用） ====================
$stats_by_date = $wpdb->get_results("
    SELECT 
        DATE(sd.created_at) as date,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    GROUP BY DATE(sd.created_at)
    ORDER BY date ASC
");

// ==================== ページ別トップ10 ====================
$stats_by_page = $wpdb->get_results("
    SELECT 
        sd.page_url,
        sd.page_title,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks,
        CASE 
            WHEN COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) / COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as ctr
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    AND sd.page_url IS NOT NULL
    AND sd.page_url != ''
    GROUP BY sd.page_url, sd.page_title
    ORDER BY clicks DESC, impressions DESC
    LIMIT 10
");

// ==================== デバイス別統計 ====================
$stats_by_device = $wpdb->get_results("
    SELECT 
        sd.device,
        COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) as impressions,
        COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) as clicks,
        CASE 
            WHEN COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END) > 0 
            THEN (COUNT(CASE WHEN sd.event_type = 'click' THEN 1 END) / COUNT(CASE WHEN sd.event_type = 'impression' THEN 1 END)) * 100
            ELSE 0
        END as ctr
    FROM {$table_stats_detail} sd
    WHERE {$date_condition}
    {$ad_condition}
    {$position_condition}
    {$category_condition}
    AND sd.device IS NOT NULL
    AND sd.device != ''
    GROUP BY sd.device
    ORDER BY impressions DESC
");

?>

<div class="wrap ji-affiliate-admin">
    <h1>広告統計情報（詳細版）</h1>
    <hr class="wp-header-end">
    
    <!-- フィルター -->
    <div class="ji-stats-filters" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">フィルター</h2>
        <form method="get" action="">
            <input type="hidden" name="page" value="ji-affiliate-stats">
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: flex-end;">
                <div>
                    <label for="period" style="display: block; margin-bottom: 5px; font-weight: 600;">期間</label>
                    <select name="period" id="period" style="min-width: 150px;">
                        <option value="today" <?php selected($period, 'today'); ?>>今日</option>
                        <option value="yesterday" <?php selected($period, 'yesterday'); ?>>昨日</option>
                        <option value="last_7_days" <?php selected($period, 'last_7_days'); ?>>過去7日間</option>
                        <option value="last_30_days" <?php selected($period, 'last_30_days'); ?>>過去30日間</option>
                        <option value="this_month" <?php selected($period, 'this_month'); ?>>今月</option>
                        <option value="last_month" <?php selected($period, 'last_month'); ?>>先月</option>
                    </select>
                </div>
                
                <div>
                    <label for="ad_filter" style="display: block; margin-bottom: 5px; font-weight: 600;">広告</label>
                    <select name="ad_filter" id="ad_filter" style="min-width: 200px;">
                        <option value="0">すべての広告</option>
                        <?php foreach ($all_ads as $ad): ?>
                            <option value="<?php echo esc_attr($ad->id); ?>" <?php selected($ad_filter, $ad->id); ?>>
                                <?php echo esc_html($ad->title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="position_filter" style="display: block; margin-bottom: 5px; font-weight: 600;">ポジション</label>
                    <select name="position_filter" id="position_filter" style="min-width: 200px;">
                        <option value="">すべてのポジション</option>
                        <?php foreach ($all_positions as $pos): ?>
                            <option value="<?php echo esc_attr($pos); ?>" <?php selected($position_filter, $pos); ?>>
                                <?php echo esc_html($pos); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="category_filter" style="display: block; margin-bottom: 5px; font-weight: 600;">カテゴリー</label>
                    <select name="category_filter" id="category_filter" style="min-width: 200px;">
                        <option value="0">すべてのカテゴリー</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($category_filter, $cat->term_id); ?>>
                                <?php echo esc_html($cat->name); ?> (<?php echo $cat->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="button button-primary">フィルター適用</button>
                    <a href="?page=ji-affiliate-stats" class="button">リセット</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- サマリー -->
    <div class="ji-stats-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div style="background: white; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">表示回数</h3>
            <p style="margin: 0; font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo number_format($summary->total_impressions); ?></p>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">クリック数</h3>
            <p style="margin: 0; font-size: 32px; font-weight: bold; color: #d63638;"><?php echo number_format($summary->total_clicks); ?></p>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">CTR</h3>
            <p style="margin: 0; font-size: 32px; font-weight: bold; color: #00a32a;"><?php echo number_format($summary->overall_ctr, 2); ?>%</p>
        </div>
        <div style="background: white; padding: 20px; border: 1px solid #c3c4c7; border-radius: 4px; text-align: center;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #666;">広告数</h3>
            <p style="margin: 0; font-size: 32px; font-weight: bold; color: #2c3338;"><?php echo number_format($summary->total_ads); ?></p>
        </div>
    </div>
    
    <!-- 日別グラフ -->
    <?php if (!empty($stats_by_date)): ?>
    <div class="ji-stats-chart" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">日別推移（<?php echo esc_html($period_label); ?>）</h2>
        <canvas id="ji-daily-chart" style="max-height: 300px;"></canvas>
    </div>
    <?php endif; ?>
    
    <!-- 広告別統計 -->
    <?php if (!empty($stats_by_ad)): ?>
    <div class="ji-stats-table" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">広告別統計</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>広告ID</th>
                    <th>広告名</th>
                    <th style="text-align: right;">表示回数</th>
                    <th style="text-align: right;">クリック数</th>
                    <th style="text-align: right;">CTR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_by_ad as $stat): ?>
                <tr>
                    <td><?php echo esc_html($stat->ad_id); ?></td>
                    <td><strong><?php echo esc_html($stat->ad_title); ?></strong></td>
                    <td style="text-align: right;"><?php echo number_format($stat->impressions); ?></td>
                    <td style="text-align: right;"><?php echo number_format($stat->clicks); ?></td>
                    <td style="text-align: right;">
                        <strong style="color: <?php echo $stat->ctr >= 2 ? '#00a32a' : '#2c3338'; ?>">
                            <?php echo number_format($stat->ctr, 2); ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- ポジション別統計 -->
    <?php if (!empty($stats_by_position)): ?>
    <div class="ji-stats-table" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">ポジション別統計</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ポジション</th>
                    <th style="text-align: right;">表示回数</th>
                    <th style="text-align: right;">クリック数</th>
                    <th style="text-align: right;">CTR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_by_position as $stat): ?>
                <tr>
                    <td><strong><?php echo esc_html($stat->position); ?></strong></td>
                    <td style="text-align: right;"><?php echo number_format($stat->impressions); ?></td>
                    <td style="text-align: right;"><?php echo number_format($stat->clicks); ?></td>
                    <td style="text-align: right;">
                        <strong style="color: <?php echo $stat->ctr >= 2 ? '#00a32a' : '#2c3338'; ?>">
                            <?php echo number_format($stat->ctr, 2); ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- カテゴリー別統計 -->
    <?php if (!empty($stats_by_category)): ?>
    <div class="ji-stats-table" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">カテゴリー別統計</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>カテゴリー</th>
                    <th style="text-align: right;">表示回数</th>
                    <th style="text-align: right;">クリック数</th>
                    <th style="text-align: right;">CTR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_by_category as $stat): ?>
                <tr>
                    <td><strong><?php echo esc_html($stat->category_name); ?></strong> (ID: <?php echo esc_html($stat->category_id); ?>)</td>
                    <td style="text-align: right;"><?php echo number_format($stat->impressions); ?></td>
                    <td style="text-align: right;"><?php echo number_format($stat->clicks); ?></td>
                    <td style="text-align: right;">
                        <strong style="color: <?php echo $stat->ctr >= 2 ? '#00a32a' : '#2c3338'; ?>">
                            <?php echo number_format($stat->ctr, 2); ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- ページ別トップ10 -->
    <?php if (!empty($stats_by_page)): ?>
    <div class="ji-stats-table" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">ページ別トップ10</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ページタイトル</th>
                    <th style="width: 200px;">URL</th>
                    <th style="text-align: right;">表示回数</th>
                    <th style="text-align: right;">クリック数</th>
                    <th style="text-align: right;">CTR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_by_page as $stat): ?>
                <tr>
                    <td><strong><?php echo esc_html($stat->page_title); ?></strong></td>
                    <td style="word-break: break-all; font-size: 11px;">
                        <a href="<?php echo esc_url($stat->page_url); ?>" target="_blank" rel="noopener">
                            <?php echo esc_html(substr($stat->page_url, 0, 50)) . (strlen($stat->page_url) > 50 ? '...' : ''); ?>
                        </a>
                    </td>
                    <td style="text-align: right;"><?php echo number_format($stat->impressions); ?></td>
                    <td style="text-align: right;"><?php echo number_format($stat->clicks); ?></td>
                    <td style="text-align: right;">
                        <strong style="color: <?php echo $stat->ctr >= 2 ? '#00a32a' : '#2c3338'; ?>">
                            <?php echo number_format($stat->ctr, 2); ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <!-- デバイス別統計 -->
    <?php if (!empty($stats_by_device)): ?>
    <div class="ji-stats-table" style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px;">
        <h2 style="margin-top: 0;">デバイス別統計</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>デバイス</th>
                    <th style="text-align: right;">表示回数</th>
                    <th style="text-align: right;">クリック数</th>
                    <th style="text-align: right;">CTR</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stats_by_device as $stat): ?>
                <tr>
                    <td><strong><?php echo $stat->device === 'mobile' ? 'スマートフォン' : ($stat->device === 'desktop' ? 'PC' : esc_html($stat->device)); ?></strong></td>
                    <td style="text-align: right;"><?php echo number_format($stat->impressions); ?></td>
                    <td style="text-align: right;"><?php echo number_format($stat->clicks); ?></td>
                    <td style="text-align: right;">
                        <strong style="color: <?php echo $stat->ctr >= 2 ? '#00a32a' : '#2c3338'; ?>">
                            <?php echo number_format($stat->ctr, 2); ?>%
                        </strong>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (empty($stats_by_ad) && empty($stats_by_position) && empty($stats_by_category)): ?>
    <div style="background: white; padding: 20px; margin: 20px 0; border: 1px solid #c3c4c7; border-radius: 4px; text-align: center;">
        <p>選択した期間・条件の統計データがありません。</p>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
jQuery(document).ready(function($) {
    <?php if (!empty($stats_by_date)): ?>
    // 日別グラフ
    var ctx = document.getElementById('ji-daily-chart');
    if (ctx) {
        ctx = ctx.getContext('2d');
        
        var dates = <?php echo json_encode(array_map(function($s) { return $s->date; }, $stats_by_date)); ?>;
        var impressions = <?php echo json_encode(array_map(function($s) { return intval($s->impressions); }, $stats_by_date)); ?>;
        var clicks = <?php echo json_encode(array_map(function($s) { return intval($s->clicks); }, $stats_by_date)); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [
                    {
                        label: '表示回数',
                        data: impressions,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'クリック数',
                        data: clicks,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>
