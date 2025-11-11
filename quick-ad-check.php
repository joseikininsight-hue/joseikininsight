<?php
/**
 * Quick Ad Database Check
 * 広告データベース即座確認ツール
 */

// WordPress環境を読み込み
require_once(__DIR__ . '/wp-load.php');

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>広告データベース即座確認</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #0066ff; border-bottom: 3px solid #0066ff; padding-bottom: 10px; }
        h2 { color: #333; margin-top: 30px; background: #f0f8ff; padding: 10px; border-left: 5px solid #0066ff; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border: 1px solid #ddd; }
        th { background: #0066ff; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .highlight { background: yellow; font-weight: bold; }
        .section { margin: 30px 0; padding: 20px; border: 2px solid #e0e0e0; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 広告データベース即座確認ツール</h1>
        <p>現在時刻: <?php echo current_time('Y-m-d H:i:s'); ?></p>

        <?php
        global $wpdb;
        $table_name_ads = $wpdb->prefix . 'ji_affiliate_ads';
        
        // 1. テーブル存在確認
        echo '<div class="section">';
        echo '<h2>1. データベーステーブル確認</h2>';
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name_ads}'");
        if ($table_exists) {
            echo '<p class="success">✓ テーブル存在: ' . $table_name_ads . '</p>';
        } else {
            echo '<p class="error">✗ テーブルが存在しません: ' . $table_name_ads . '</p>';
            echo '</div></div></body></html>';
            exit;
        }
        echo '</div>';
        
        // 2. 全広告データを取得
        echo '<div class="section">';
        echo '<h2>2. 登録されている全広告</h2>';
        $all_ads = $wpdb->get_results("SELECT * FROM {$table_name_ads}");
        $total_ads = count($all_ads);
        echo '<p>登録広告数: <strong>' . $total_ads . '件</strong></p>';
        
        if ($total_ads === 0) {
            echo '<p class="error">✗ 広告が1件も登録されていません</p>';
        } else {
            echo '<table>';
            echo '<tr><th>ID</th><th>タイトル</th><th>ステータス</th><th>表示位置 (positions)</th><th>対象デバイス</th><th>対象ページ</th><th>対象カテゴリー</th><th>優先度</th></tr>';
            
            foreach ($all_ads as $ad) {
                $positions_match = false;
                if (!empty($ad->positions) && strpos($ad->positions, 'single_grant_sidebar_bottom') !== false) {
                    $positions_match = true;
                }
                
                $row_style = $positions_match ? ' style="background: #d4edda;"' : '';
                
                echo '<tr' . $row_style . '>';
                echo '<td>' . $ad->id . '</td>';
                echo '<td>' . esc_html($ad->title) . '</td>';
                echo '<td>' . ($ad->status === 'active' ? '<span class="success">有効</span>' : '<span class="warning">無効</span>') . '</td>';
                
                // 表示位置を強調表示
                $positions_display = !empty($ad->positions) ? $ad->positions : '(未設定)';
                if ($positions_match) {
                    $positions_display = '<span class="highlight">' . $positions_display . '</span>';
                }
                echo '<td>' . $positions_display . '</td>';
                
                echo '<td>' . (!empty($ad->device_target) ? $ad->device_target : 'all') . '</td>';
                echo '<td>' . (!empty($ad->target_pages) ? $ad->target_pages : '(全ページ)') . '</td>';
                echo '<td>' . (!empty($ad->target_categories) ? $ad->target_categories : '(全カテゴリー)') . '</td>';
                echo '<td>' . (!empty($ad->priority) ? $ad->priority : '0') . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
        }
        echo '</div>';
        
        // 3. single_grant_sidebar_bottom にマッチする広告を確認
        echo '<div class="section">';
        echo '<h2>3. single_grant_sidebar_bottom 用広告の検索</h2>';
        
        $position = 'single_grant_sidebar_bottom';
        $device = 'desktop'; // デスクトップと仮定
        $current_datetime = current_time('mysql');
        
        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name_ads}
            WHERE FIND_IN_SET(%s, REPLACE(positions, ' ', '')) > 0
            AND status = 'active'
            AND (device_target = 'all' OR device_target = %s)
            AND (start_date IS NULL OR start_date <= %s)
            AND (end_date IS NULL OR end_date >= %s)
            ORDER BY priority DESC
            LIMIT 5",
            $position, $device, $current_datetime, $current_datetime
        );
        
        echo '<p><strong>実行クエリ:</strong></p>';
        echo '<pre>' . esc_html($query) . '</pre>';
        
        $matching_ads = $wpdb->get_results($query);
        $match_count = count($matching_ads);
        
        if ($match_count > 0) {
            echo '<p class="success">✓ マッチする広告: ' . $match_count . '件</p>';
            echo '<table>';
            echo '<tr><th>ID</th><th>タイトル</th><th>表示位置</th><th>広告コード（抜粋）</th></tr>';
            foreach ($matching_ads as $ad) {
                echo '<tr>';
                echo '<td>' . $ad->id . '</td>';
                echo '<td>' . esc_html($ad->title) . '</td>';
                echo '<td>' . esc_html($ad->positions) . '</td>';
                echo '<td>' . esc_html(substr($ad->ad_code, 0, 100)) . '...</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">✗ マッチする広告が見つかりませんでした</p>';
            echo '<p><strong>原因の可能性:</strong></p>';
            echo '<ul>';
            echo '<li>広告の「表示位置」に <code>single_grant_sidebar_bottom</code> が選択されていない</li>';
            echo '<li>広告のステータスが「無効」になっている</li>';
            echo '<li>広告の掲載期間が過ぎている</li>';
            echo '<li>対象デバイスが一致していない</li>';
            echo '</ul>';
        }
        echo '</div>';
        
        // 4. 診断結果
        echo '<div class="section">';
        echo '<h2>4. 診断結果と推奨アクション</h2>';
        
        if ($match_count > 0) {
            echo '<p class="success">✓ 広告は正しく設定されています</p>';
            echo '<p><strong>表示されない場合の確認項目:</strong></p>';
            echo '<ul>';
            echo '<li>ブラウザキャッシュをクリア (Shift + F5)</li>';
            echo '<li>Cloudflare等のCDNキャッシュをクリア</li>';
            echo '<li>AdBlockなどの広告ブロッカーを無効化</li>';
            echo '<li>WordPress管理者でログイン中は広告が非表示になる設定がないか確認</li>';
            echo '</ul>';
        } else {
            echo '<p class="error">✗ 問題発見: single_grant_sidebar_bottom に該当する広告がありません</p>';
            echo '<p><strong>解決方法:</strong></p>';
            echo '<ol>';
            echo '<li>WordPress管理画面 → アフィリエイト広告 → 広告一覧</li>';
            echo '<li>広告ID 3, 4, 6 のいずれかを編集</li>';
            echo '<li>「表示位置」セクションで <code>single_grant_sidebar_bottom</code> にチェックを入れる</li>';
            echo '<li>ステータスが「有効」であることを確認</li>';
            echo '<li>保存</li>';
            echo '</ol>';
        }
        echo '</div>';
        
        // 5. 追加情報
        echo '<div class="section">';
        echo '<h2>5. ji_display_ad() 関数確認</h2>';
        if (function_exists('ji_display_ad')) {
            echo '<p class="success">✓ ji_display_ad() 関数は定義されています</p>';
        } else {
            echo '<p class="error">✗ ji_display_ad() 関数が定義されていません</p>';
        }
        
        echo '<h2>6. JI_Affiliate_Ad_Manager クラス確認</h2>';
        if (class_exists('JI_Affiliate_Ad_Manager')) {
            echo '<p class="success">✓ JI_Affiliate_Ad_Manager クラスは定義されています</p>';
        } else {
            echo '<p class="error">✗ JI_Affiliate_Ad_Manager クラスが定義されていません</p>';
        }
        echo '</div>';
        ?>
        
        <div class="section" style="background: #f0f8ff; border: 2px solid #0066ff;">
            <h2 style="background: transparent;">📋 結論</h2>
            <?php if ($match_count > 0): ?>
                <p class="success" style="font-size: 18px;">✓ データベース設定は正常です。広告が表示されない場合は、キャッシュまたは広告ブロッカーが原因の可能性が高いです。</p>
            <?php else: ?>
                <p class="error" style="font-size: 18px;">✗ データベースに「single_grant_sidebar_bottom」用の広告が登録されていません。WordPress管理画面で広告の表示位置を設定してください。</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
