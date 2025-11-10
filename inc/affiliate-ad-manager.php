<?php
/**
 * Affiliate Ad Manager System
 * アフィリエイト広告管理システム
 * 
 * Features:
 * - WordPress管理画面での広告管理
 * - 複数の広告位置対応（サイドバー、コンテンツ内など）
 * - クリック統計・表示統計
 * - A/Bテスト機能
 * - スケジュール配信
 * 
 * @package Joseikin_Insight
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// デバッグ: ファイル読み込み開始
error_log('🔵 affiliate-ad-manager.php: File loaded at ' . date('Y-m-d H:i:s'));

class JI_Affiliate_Ad_Manager {
    
    private $table_name_ads;
    private $table_name_stats;
    private $table_name_stats_detail; // 詳細統計テーブル
    
    public function __construct() {
        error_log('🟢 JI_Affiliate_Ad_Manager: __construct() called');
        
        global $wpdb;
        $this->table_name_ads = $wpdb->prefix . 'ji_affiliate_ads';
        $this->table_name_stats = $wpdb->prefix . 'ji_affiliate_stats';
        $this->table_name_stats_detail = $wpdb->prefix . 'ji_affiliate_stats_detail';
        
        error_log('🟢 JI_Affiliate_Ad_Manager: Table names set - ads: ' . $this->table_name_ads);
        
        // フック登録
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_ji_save_ad', array($this, 'ajax_save_ad'));
        add_action('wp_ajax_ji_get_ad', array($this, 'ajax_get_ad')); // 新規: 広告データ取得
        add_action('wp_ajax_ji_delete_ad', array($this, 'ajax_delete_ad'));
        add_action('wp_ajax_ji_get_ad_stats', array($this, 'ajax_get_ad_stats'));
        add_action('wp_ajax_ji_track_ad_impression', array($this, 'ajax_track_impression'));
        add_action('wp_ajax_nopriv_ji_track_ad_impression', array($this, 'ajax_track_impression'));
        add_action('wp_ajax_ji_track_ad_click', array($this, 'ajax_track_click'));
        add_action('wp_ajax_nopriv_ji_track_ad_click', array($this, 'ajax_track_click'));
        
        error_log('🟢 JI_Affiliate_Ad_Manager: __construct() completed');
    }
    
    /**
     * 初期化
     */
    public function init() {
        // テーブル作成
        $this->create_tables();
    }
    
    /**
     * データベーステーブル作成
     */
    private function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // 広告テーブル
        $sql_ads = "CREATE TABLE IF NOT EXISTS {$this->table_name_ads} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            ad_type varchar(50) NOT NULL DEFAULT 'html',
            content longtext NOT NULL,
            link_url varchar(500) DEFAULT '',
            positions text NOT NULL,
            target_pages text DEFAULT NULL,
            device_target varchar(20) NOT NULL DEFAULT 'all',
            status varchar(20) NOT NULL DEFAULT 'active',
            priority int(11) NOT NULL DEFAULT 0,
            start_date datetime DEFAULT NULL,
            end_date datetime DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY priority (priority),
            KEY device_target (device_target)
        ) $charset_collate;";
        
        // 統計テーブル（既存の集計用）
        $sql_stats = "CREATE TABLE IF NOT EXISTS {$this->table_name_stats} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ad_id bigint(20) NOT NULL,
            date date NOT NULL,
            impressions int(11) NOT NULL DEFAULT 0,
            clicks int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY ad_date (ad_id, date),
            KEY ad_id (ad_id),
            KEY date (date)
        ) $charset_collate;";
        
        // 詳細統計テーブル（新規: ページURL、カテゴリー、デバイス等の詳細情報）
        $sql_stats_detail = "CREATE TABLE IF NOT EXISTS {$this->table_name_stats_detail} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ad_id bigint(20) NOT NULL,
            event_type enum('impression','click') NOT NULL DEFAULT 'impression',
            page_url varchar(500) DEFAULT NULL,
            page_title varchar(500) DEFAULT NULL,
            post_id bigint(20) DEFAULT NULL,
            category_id bigint(20) DEFAULT NULL,
            category_name varchar(200) DEFAULT NULL,
            position varchar(100) DEFAULT NULL,
            device varchar(20) DEFAULT NULL,
            user_agent text DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            referer varchar(500) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY ad_id (ad_id),
            KEY event_type (event_type),
            KEY post_id (post_id),
            KEY category_id (category_id),
            KEY position (position),
            KEY device (device),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_ads);
        dbDelta($sql_stats);
        dbDelta($sql_stats_detail);
        
        // デバイスターゲット列を追加（既存テーブル用）
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$this->table_name_ads} LIKE 'device_target'"
        );
        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE {$this->table_name_ads} 
                ADD COLUMN device_target varchar(20) NOT NULL DEFAULT 'all' AFTER target_pages,
                ADD KEY device_target (device_target)"
            );
        }
        
        // positionカラムをpositionsに変更（複数位置対応）
        $position_column = $wpdb->get_results(
            "SHOW COLUMNS FROM {$this->table_name_ads} LIKE 'position'"
        );
        if (!empty($position_column)) {
            // 既存のpositionカラムをpositionsに変更
            $wpdb->query(
                "ALTER TABLE {$this->table_name_ads} 
                CHANGE COLUMN position positions text NOT NULL"
            );
        }
        
        // target_categories カラムを追加（カテゴリー別広告配信）
        $target_categories_column = $wpdb->get_results(
            "SHOW COLUMNS FROM {$this->table_name_ads} LIKE 'target_categories'"
        );
        if (empty($target_categories_column)) {
            $wpdb->query(
                "ALTER TABLE {$this->table_name_ads} 
                ADD COLUMN target_categories text DEFAULT NULL AFTER target_pages,
                ADD KEY target_categories (target_categories(100))"
            );
        }
    }
    
    /**
     * 管理メニュー追加
     */
    public function add_admin_menu() {
        add_menu_page(
            'アフィリエイト広告管理',
            'アフィリエイト広告',
            'manage_options',
            'ji-affiliate-ads',
            array($this, 'admin_page'),
            'dashicons-megaphone',
            25
        );
        
        add_submenu_page(
            'ji-affiliate-ads',
            '広告一覧',
            '広告一覧',
            'manage_options',
            'ji-affiliate-ads',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'ji-affiliate-ads',
            '統計情報',
            '統計情報',
            'manage_options',
            'ji-affiliate-stats',
            array($this, 'stats_page')
        );
        
        add_submenu_page(
            'ji-affiliate-ads',
            '設定',
            '設定',
            'manage_options',
            'ji-affiliate-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * 管理画面アセット読み込み
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'ji-affiliate') === false) {
            return;
        }
        
        wp_enqueue_style('ji-admin-ads', get_template_directory_uri() . '/assets/css/admin-ads.css', array(), '1.0.0');
        wp_enqueue_script('ji-admin-ads', get_template_directory_uri() . '/assets/js/admin-ads.js', array('jquery'), '1.0.0', true);
        
        wp_localize_script('ji-admin-ads', 'jiAdminAds', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ji_ad_nonce'),
        ));
    }
    
    /**
     * 広告管理ページ
     */
    public function admin_page() {
        global $wpdb;
        
        $ads = $wpdb->get_results(
            "SELECT * FROM {$this->table_name_ads} ORDER BY priority DESC, id DESC"
        );
        
        include get_template_directory() . '/inc/admin-templates/affiliate-ads-list.php';
    }
    
    /**
     * 統計ページ
     */
    public function stats_page() {
        global $wpdb;
        
        // 期間フィルター
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30';
        $period_label = array(
            '7' => '過去7日間',
            '30' => '過去30日間',
            '90' => '過去90日間',
            '365' => '過去365日間'
        );
        
        // 広告フィルター
        $ad_id = isset($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;
        
        // 基本統計を取得
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                a.id,
                a.title,
                a.positions,
                SUM(s.impressions) as total_impressions,
                SUM(s.clicks) as total_clicks,
                CASE 
                    WHEN SUM(s.impressions) > 0 
                    THEN ROUND((SUM(s.clicks) / SUM(s.impressions)) * 100, 2)
                    ELSE 0
                END as ctr
            FROM {$this->table_name_ads} a
            LEFT JOIN {$this->table_name_stats} s ON a.id = s.ad_id
            WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            GROUP BY a.id
            ORDER BY total_clicks DESC",
            $period
        ));
        
        // 詳細統計を取得（指定期間）
        $detailed_stats = array();
        if ($ad_id > 0) {
            // 特定の広告の詳細統計
            $detailed_stats = $wpdb->get_results($wpdb->prepare(
                "SELECT 
                    DATE(created_at) as date,
                    event_type,
                    position,
                    category_name,
                    page_url,
                    device,
                    COUNT(*) as count
                FROM {$this->table_name_stats_detail}
                WHERE ad_id = %d 
                AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
                GROUP BY DATE(created_at), event_type, position, category_name, device
                ORDER BY created_at DESC",
                $ad_id,
                $period
            ));
        }
        
        // 日別統計データ（グラフ用）
        $daily_stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                s.date,
                SUM(s.impressions) as impressions,
                SUM(s.clicks) as clicks
            FROM {$this->table_name_stats} s
            WHERE s.date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            GROUP BY s.date
            ORDER BY s.date ASC",
            $period
        ));
        
        // 広告一覧（フィルター用）
        $all_ads = $wpdb->get_results(
            "SELECT id, title FROM {$this->table_name_ads} ORDER BY title ASC"
        );
        
        include get_template_directory() . '/inc/admin-templates/affiliate-stats.php';
    }
    
    /**
     * 設定ページ
     */
    public function settings_page() {
        include get_template_directory() . '/inc/admin-templates/affiliate-settings.php';
    }
    
    /**
     * AJAX: 広告保存
     */
    public function ajax_save_ad() {
        check_ajax_referer('ji_ad_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('権限がありません');
        }
        
        global $wpdb;
        
        $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;
        
        // 複数位置を配列として受け取り、カンマ区切りで保存
        $positions = isset($_POST['positions']) && is_array($_POST['positions']) 
            ? $_POST['positions'] 
            : (isset($_POST['position']) ? array($_POST['position']) : array());
        $positions_string = implode(',', array_map('sanitize_text_field', $positions));
        
        // 対象ページも配列として受け取り、カンマ区切りで保存
        $target_pages = isset($_POST['target_pages']) && is_array($_POST['target_pages']) 
            ? $_POST['target_pages'] 
            : array();
        // 空文字列要素を除外
        $target_pages = array_filter($target_pages, function($page) {
            return !empty($page);
        });
        $target_pages_string = implode(',', array_map('sanitize_text_field', $target_pages));
        
        // 対象カテゴリーも配列として受け取り、カンマ区切りで保存
        $target_categories = isset($_POST['target_categories']) && is_array($_POST['target_categories']) 
            ? $_POST['target_categories'] 
            : array();
        // 空文字列要素を除外
        $target_categories = array_filter($target_categories, function($cat) {
            return !empty($cat);
        });
        $target_categories_string = implode(',', array_map('sanitize_text_field', $target_categories));
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'ad_type' => sanitize_text_field($_POST['ad_type']),
            'content' => wp_kses_post($_POST['content']),
            'link_url' => esc_url_raw($_POST['link_url']),
            'positions' => $positions_string,
            'target_pages' => $target_pages_string,
            'target_categories' => $target_categories_string,
            'device_target' => sanitize_text_field($_POST['device_target']),
            'status' => sanitize_text_field($_POST['status']),
            'priority' => intval($_POST['priority']),
            'start_date' => !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : null,
            'end_date' => !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : null,
        );
        
        if ($ad_id > 0) {
            // 更新
            $result = $wpdb->update($this->table_name_ads, $data, array('id' => $ad_id));
        } else {
            // 新規作成
            $result = $wpdb->insert($this->table_name_ads, $data);
            $ad_id = $wpdb->insert_id;
        }
        
        if ($result === false) {
            wp_send_json_error('保存に失敗しました');
        }
        
        wp_send_json_success(array(
            'message' => '保存しました',
            'ad_id' => $ad_id
        ));
    }
    
    /**
     * AJAX: 広告データ取得（編集用）
     */
    public function ajax_get_ad() {
        check_ajax_referer('ji_ad_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('権限がありません');
        }
        
        global $wpdb;
        
        $ad_id = intval($_POST['ad_id']);
        
        $ad = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name_ads} WHERE id = %d",
            $ad_id
        ));
        
        if (!$ad) {
            wp_send_json_error('広告が見つかりません');
        }
        
        // positions をarray に変換
        $ad->positions_array = explode(',', $ad->positions);
        
        // target_pages を array に変換
        $ad->target_pages_array = !empty($ad->target_pages) ? explode(',', $ad->target_pages) : array();
        
        // target_categories を array に変換
        $ad->target_categories_array = !empty($ad->target_categories) ? explode(',', $ad->target_categories) : array();
        
        wp_send_json_success($ad);
    }
    
    /**
     * AJAX: 広告削除
     */
    public function ajax_delete_ad() {
        check_ajax_referer('ji_ad_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('権限がありません');
        }
        
        global $wpdb;
        
        $ad_id = intval($_POST['ad_id']);
        
        // 統計データも削除
        $wpdb->delete($this->table_name_stats, array('ad_id' => $ad_id));
        
        $result = $wpdb->delete($this->table_name_ads, array('id' => $ad_id));
        
        if ($result === false) {
            wp_send_json_error('削除に失敗しました');
        }
        
        wp_send_json_success('削除しました');
    }
    
    /**
     * AJAX: 広告統計取得
     */
    public function ajax_get_ad_stats() {
        check_ajax_referer('ji_ad_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('権限がありません');
        }
        
        global $wpdb;
        
        $ad_id = intval($_POST['ad_id']);
        $days = isset($_POST['days']) ? intval($_POST['days']) : 30;
        
        $stats = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                date,
                impressions,
                clicks,
                CASE 
                    WHEN impressions > 0 
                    THEN ROUND((clicks / impressions) * 100, 2)
                    ELSE 0
                END as ctr
            FROM {$this->table_name_stats}
            WHERE ad_id = %d
            AND date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            ORDER BY date ASC",
            $ad_id,
            $days
        ));
        
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX: インプレッション記録（詳細情報付き）
     */
    public function ajax_track_impression() {
        $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;
        
        if ($ad_id <= 0) {
            wp_send_json_error('Invalid ad ID');
        }
        
        global $wpdb;
        
        $today = current_time('Y-m-d');
        
        // 既存の集計テーブルを更新
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$this->table_name_stats} (ad_id, date, impressions, clicks)
            VALUES (%d, %s, 1, 0)
            ON DUPLICATE KEY UPDATE impressions = impressions + 1",
            $ad_id,
            $today
        ));
        
        // 詳細統計テーブルに記録
        $this->track_detailed_event($ad_id, 'impression', $_POST);
        
        wp_send_json_success();
    }
    
    /**
     * AJAX: クリック記録（詳細情報付き）
     */
    public function ajax_track_click() {
        $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;
        
        if ($ad_id <= 0) {
            wp_send_json_error('Invalid ad ID');
        }
        
        global $wpdb;
        
        $today = current_time('Y-m-d');
        
        // 既存の集計テーブルを更新
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$this->table_name_stats} (ad_id, date, impressions, clicks)
            VALUES (%d, %s, 0, 1)
            ON DUPLICATE KEY UPDATE clicks = clicks + 1",
            $ad_id,
            $today
        ));
        
        // 詳細統計テーブルに記録
        $this->track_detailed_event($ad_id, 'click', $_POST);
        
        wp_send_json_success();
    }
    
    /**
     * 詳細イベントトラッキング
     * 
     * @param int $ad_id 広告ID
     * @param string $event_type イベントタイプ（impression/click）
     * @param array $data POSTデータ
     */
    private function track_detailed_event($ad_id, $event_type, $data) {
        global $wpdb;
        
        // ページ情報を取得
        $page_url = isset($data['page_url']) ? esc_url_raw($data['page_url']) : '';
        $page_title = isset($data['page_title']) ? sanitize_text_field($data['page_title']) : '';
        $post_id = isset($data['post_id']) ? intval($data['post_id']) : null;
        $category_id = isset($data['category_id']) ? intval($data['category_id']) : null;
        $category_name = isset($data['category_name']) ? sanitize_text_field($data['category_name']) : null;
        $position = isset($data['position']) ? sanitize_text_field($data['position']) : null;
        
        // デバイス情報
        $device = $this->detect_device();
        
        // ユーザーエージェント
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        
        // IPアドレス
        $ip_address = $this->get_client_ip();
        
        // リファラー
        $referer = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw($_SERVER['HTTP_REFERER']) : '';
        
        // データベースに挿入
        $wpdb->insert(
            $this->table_name_stats_detail,
            array(
                'ad_id' => $ad_id,
                'event_type' => $event_type,
                'page_url' => $page_url,
                'page_title' => $page_title,
                'post_id' => $post_id,
                'category_id' => $category_id,
                'category_name' => $category_name,
                'position' => $position,
                'device' => $device,
                'user_agent' => $user_agent,
                'ip_address' => $ip_address,
                'referer' => $referer,
                'created_at' => current_time('mysql')
            ),
            array(
                '%d', // ad_id
                '%s', // event_type
                '%s', // page_url
                '%s', // page_title
                '%d', // post_id
                '%d', // category_id
                '%s', // category_name
                '%s', // position
                '%s', // device
                '%s', // user_agent
                '%s', // ip_address
                '%s', // referer
                '%s'  // created_at
            )
        );
    }
    
    /**
     * クライアントIPアドレスを取得
     * 
     * @return string IPアドレス
     */
    private function get_client_ip() {
        $ip = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * デバイスタイプを検出
     * 
     * @return string 'mobile' または 'desktop'
     */
    private function detect_device() {
        if (wp_is_mobile()) {
            return 'mobile';
        }
        return 'desktop';
    }
    
    /**
     * 指定位置の広告を取得（複数位置対応 + カテゴリー対応）
     * 
     * @param string $position 広告位置
     * @param array $options オプション（category_ids, page_type等）
     * @return object|null 広告オブジェクト
     */
    public function get_ad_for_position($position, $options = array()) {
        global $wpdb;
        
        $current_datetime = current_time('mysql');
        $device = $this->detect_device();
        
        // オプションから情報を取得
        $category_ids = isset($options['category_ids']) ? $options['category_ids'] : array();
        $page_type = isset($options['page_type']) ? $options['page_type'] : '';
        
        // デバッグログ: 広告取得開始
        error_log("🔍 [Ad Manager] get_ad_for_position called");
        error_log("  Position: " . $position);
        error_log("  Page Type: " . $page_type);
        error_log("  Device: " . $device);
        error_log("  Category IDs: " . implode(',', $category_ids));
        
        // 優先順位スコアを計算するためのCASE文を構築
        // 優先順位: 1=カテゴリー一致, 2=ページ一致, 3=指定なし
        $priority_score_case = "CASE ";
        
        // カテゴリー条件
        if (!empty($category_ids)) {
            $category_conditions = array();
            foreach ($category_ids as $cat_id) {
                $category_conditions[] = "FIND_IN_SET(%s, REPLACE(a.target_categories, ' ', '')) > 0";
            }
            $category_match = "(" . implode(' OR ', $category_conditions) . ")";
            $priority_score_case .= "WHEN {$category_match} THEN 1 ";
        }
        
        // ページタイプ条件
        if (!empty($page_type)) {
            $priority_score_case .= "WHEN FIND_IN_SET(%s, REPLACE(a.target_pages, ' ', '')) > 0 THEN 2 ";
        }
        
        // 指定なし（全体表示）
        $priority_score_case .= "WHEN (a.target_categories IS NULL OR a.target_categories = '') AND (a.target_pages IS NULL OR a.target_pages = '') THEN 3 ";
        $priority_score_case .= "ELSE 999 END"; // マッチしない場合は除外
        
        // WHERE条件: priority_score が 999 でないもののみ
        $category_condition = " AND ({$priority_score_case}) < 999";
        
        // 自動最適化が有効かチェック
        $auto_optimize = get_option('ji_affiliate_auto_optimize', '0');
        error_log("  Auto Optimize: " . $auto_optimize);
        
        if ($auto_optimize === '1') {
            // CTR based 最適化: 過去30日のCTRでソート + 優先順位スコア
            $base_query = "SELECT 
                    a.*,
                    {$priority_score_case} as priority_score,
                    COALESCE(
                        (SELECT SUM(s.clicks) FROM {$this->table_name_stats} s 
                         WHERE s.ad_id = a.id 
                         AND s.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        ), 0
                    ) as total_clicks,
                    COALESCE(
                        (SELECT SUM(s.impressions) FROM {$this->table_name_stats} s 
                         WHERE s.ad_id = a.id 
                         AND s.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                        ), 0
                    ) as total_impressions,
                    CASE 
                        WHEN COALESCE(
                            (SELECT SUM(s.impressions) FROM {$this->table_name_stats} s 
                             WHERE s.ad_id = a.id 
                             AND s.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                            ), 0
                        ) > 0 
                        THEN (
                            COALESCE(
                                (SELECT SUM(s.clicks) FROM {$this->table_name_stats} s 
                                 WHERE s.ad_id = a.id 
                                 AND s.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                ), 0
                            ) / COALESCE(
                                (SELECT SUM(s.impressions) FROM {$this->table_name_stats} s 
                                 WHERE s.ad_id = a.id 
                                 AND s.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                ), 0
                            )
                        ) * 100
                        ELSE 0
                    END as ctr
                FROM {$this->table_name_ads} a
                WHERE FIND_IN_SET(%s, REPLACE(a.positions, ' ', '')) > 0
                AND a.status = 'active'
                AND (a.device_target = 'all' OR a.device_target = %s)
                AND (a.start_date IS NULL OR a.start_date <= %s)
                AND (a.end_date IS NULL OR a.end_date >= %s)
                {$category_condition}
                ORDER BY 
                    priority_score ASC,
                    a.priority DESC,
                    ctr DESC,
                    RAND()
                LIMIT 1";
            
            $prepare_args = array($position, $device, $current_datetime, $current_datetime);
            // カテゴリーIDを追加
            if (!empty($category_ids)) {
                $prepare_args = array_merge($prepare_args, $category_ids);
            }
            // ページタイプを追加
            if (!empty($page_type)) {
                $prepare_args[] = $page_type;
            }
            $query = $wpdb->prepare($base_query, $prepare_args);
        } else {
            // 通常モード: 優先順位スコア + 優先度 + ランダム
            $base_query = "SELECT a.*, {$priority_score_case} as priority_score 
                FROM {$this->table_name_ads} a
                WHERE FIND_IN_SET(%s, REPLACE(positions, ' ', '')) > 0
                AND status = 'active'
                AND (device_target = 'all' OR device_target = %s)
                AND (start_date IS NULL OR start_date <= %s)
                AND (end_date IS NULL OR end_date >= %s)
                {$category_condition}
                ORDER BY priority_score ASC, priority DESC, RAND()
                LIMIT 1";
            
            $prepare_args = array_merge(
                array($position, $device, $current_datetime, $current_datetime),
                $category_ids
            );
            if (!empty($page_type)) {
                $prepare_args[] = $page_type;
            }
            $query = $wpdb->prepare($base_query, $prepare_args);
        }
        
        // デバッグログ: クエリを記録
        error_log("  Query: " . $query);
        
        $ad = $wpdb->get_row($query);
        
        // デバッグログ: 結果を記録
        if ($ad) {
            $priority_score = isset($ad->priority_score) ? $ad->priority_score : 'N/A';
            error_log("  ✅ Ad Found: ID=" . $ad->id . ", Title=" . $ad->title . ", Priority Score=" . $priority_score);
            error_log("     Matching Type: " . ($priority_score == 1 ? 'カテゴリー一致' : ($priority_score == 2 ? 'ページ一致' : 'デフォルト表示')));
        } else {
            error_log("  ❌ No Ad Found");
            // 該当する広告がないか確認
            $all_ads = $wpdb->get_results("SELECT id, title, positions, status, target_categories, target_pages FROM {$this->table_name_ads}");
            error_log("  Total Ads in DB: " . count($all_ads));
            foreach ($all_ads as $test_ad) {
                error_log("    - ID:" . $test_ad->id . " Title:" . $test_ad->title . " Positions:" . $test_ad->positions . " Status:" . $test_ad->status);
                error_log("      Categories:" . ($test_ad->target_categories ?: 'なし') . " Pages:" . ($test_ad->target_pages ?: 'なし'));
            }
        }
        
        return $ad;
    }
    
    /**
     * 広告HTML出力
     * 
     * @param string $position 広告位置
     * @param array $options オプション（category_ids, page_type等）
     * @return string 広告HTML
     */
    public function render_ad($position, $options = array()) {
        $category_ids = isset($options['category_ids']) ? $options['category_ids'] : array();
        $page_type = isset($options['page_type']) ? $options['page_type'] : '';
        
        error_log("📺 [Ad Manager] render_ad called - Position: {$position}, Page Type: {$page_type}, Categories: " . implode(',', $category_ids));
        
        $ad = $this->get_ad_for_position($position, $options);
        
        if (!$ad) {
            error_log("  ⚠️ No ad to render");
            return '';
        }
        
        error_log("  ✅ Rendering ad: " . $ad->title);
        
        // ページ情報を取得
        global $post;
        $page_url = is_object($post) ? get_permalink($post->ID) : '';
        $page_title = is_object($post) ? get_the_title($post->ID) : '';
        $post_id = is_object($post) ? $post->ID : 0;
        
        // カテゴリー情報を取得
        $category_id = !empty($category_ids) ? $category_ids[0] : '';
        $category_name = '';
        if (!empty($category_id)) {
            // カテゴリーIDの形式をチェック（例: 'grant_category_1', 'column_category_2', 'category_3'）
            if (strpos($category_id, 'grant_category_') === 0) {
                $term_id = str_replace('grant_category_', '', $category_id);
                $term = get_term($term_id, 'grant_category');
                $category_name = !is_wp_error($term) && $term ? $term->name : '';
            } elseif (strpos($category_id, 'column_category_') === 0) {
                $term_id = str_replace('column_category_', '', $category_id);
                $term = get_term($term_id, 'column_category');
                $category_name = !is_wp_error($term) && $term ? $term->name : '';
            } elseif (strpos($category_id, 'category_') === 0) {
                $term_id = str_replace('category_', '', $category_id);
                $category = get_category($term_id);
                $category_name = $category ? $category->name : '';
            }
        }
        
        ob_start();
        ?>
        <div class="ji-affiliate-ad" 
             data-ad-id="<?php echo esc_attr($ad->id); ?>"
             data-position="<?php echo esc_attr($position); ?>"
             data-page-url="<?php echo esc_attr($page_url); ?>"
             data-page-title="<?php echo esc_attr($page_title); ?>"
             data-post-id="<?php echo esc_attr($post_id); ?>"
             data-category-id="<?php echo esc_attr($category_id); ?>"
             data-category-name="<?php echo esc_attr($category_name); ?>">
            
            <?php if ($ad->ad_type === 'html'): ?>
                <?php echo $ad->content; ?>
            <?php elseif ($ad->ad_type === 'image'): ?>
                <a href="<?php echo esc_url($ad->link_url); ?>" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="ji-ad-link"
                   data-ad-id="<?php echo esc_attr($ad->id); ?>">
                    <?php echo wp_kses_post($ad->content); ?>
                </a>
            <?php elseif ($ad->ad_type === 'script'): ?>
                <?php echo $ad->content; ?>
            <?php endif; ?>
            
        </div>
        
        <script>
        (function() {
            var adContainer = document.querySelector('[data-ad-id="<?php echo intval($ad->id); ?>"][data-position="<?php echo esc_js($position); ?>"]');
            var trackingData = {
                ad_id: <?php echo intval($ad->id); ?>,
                position: adContainer.getAttribute('data-position'),
                page_url: adContainer.getAttribute('data-page-url'),
                page_title: adContainer.getAttribute('data-page-title'),
                post_id: adContainer.getAttribute('data-post-id'),
                category_id: adContainer.getAttribute('data-category-id'),
                category_name: adContainer.getAttribute('data-category-name')
            };
            
            // インプレッション追跡
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function($) {
                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', Object.assign({
                        action: 'ji_track_ad_impression'
                    }, trackingData));
                });
            }
            
            // クリック追跡
            document.querySelectorAll('[data-ad-id="<?php echo intval($ad->id); ?>"] a').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (typeof jQuery !== 'undefined') {
                        jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', Object.assign({
                            action: 'ji_track_ad_click'
                        }, trackingData));
                    }
                });
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

// インスタンス化
error_log('🟡 affiliate-ad-manager.php: About to instantiate JI_Affiliate_Ad_Manager');
try {
    new JI_Affiliate_Ad_Manager();
    error_log('🟢 affiliate-ad-manager.php: JI_Affiliate_Ad_Manager instantiated successfully');
} catch (Exception $e) {
    error_log('🔴 affiliate-ad-manager.php: Failed to instantiate - ' . $e->getMessage());
}

/**
 * ヘルパー関数: 広告表示（カテゴリー対応版）
 * 
 * @param string $position 広告位置
 * @param array $options オプション（category_ids, page_type等）
 */
function ji_display_ad($position, $options = array()) {
    // 後方互換性のため、$optionsが文字列の場合はpage_typeとして処理
    if (is_string($options)) {
        $options = array('page_type' => $options);
    }
    
    // シングルページの場合、自動的にカテゴリーを取得
    if (is_single() && !isset($options['category_ids'])) {
        global $post;
        $category_ids = array();
        
        // 投稿タイプを確認
        $post_type = get_post_type($post->ID);
        
        if ($post_type === 'grant') {
            // 助成金の場合: grant_category タクソノミーを取得
            $grant_categories = wp_get_post_terms($post->ID, 'grant_category');
            if (!empty($grant_categories) && !is_wp_error($grant_categories)) {
                foreach ($grant_categories as $category) {
                    $category_ids[] = 'grant_category_' . $category->term_id;
                }
            }
        } elseif ($post_type === 'column') {
            // コラムの場合: column_category タクソノミーを取得
            $column_categories = wp_get_post_terms($post->ID, 'column_category');
            if (!empty($column_categories) && !is_wp_error($column_categories)) {
                foreach ($column_categories as $category) {
                    $category_ids[] = 'column_category_' . $category->term_id;
                }
            }
        } else {
            // 標準投稿の場合: 通常のカテゴリーを取得
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category_ids[] = 'category_' . $category->term_id;
                }
            }
        }
        
        $options['category_ids'] = $category_ids;
    }
    
    error_log('🟣 ji_display_ad() called - position: ' . $position . ', options: ' . json_encode($options));
    
    global $wpdb;
    $manager = new JI_Affiliate_Ad_Manager();
    echo $manager->render_ad($position, $options);
    
    error_log('🟣 ji_display_ad() completed');
}

error_log('🔵 affiliate-ad-manager.php: File execution completed, ji_display_ad function defined');
