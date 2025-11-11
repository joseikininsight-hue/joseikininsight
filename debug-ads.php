<?php
/**
 * Ad Display Debug Tool
 * 広告表示デバッグツール
 * 
 * このファイルをWordPressルートに配置し、ブラウザで直接アクセスしてください。
 * URL: https://joseikin-insight.com/debug-ads.php
 */

// WordPress環境を読み込み
require_once __DIR__ . '/wp-load.php';

// HTML出力開始
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>広告表示デバッグツール</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .section {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #0066ff;
            padding-bottom: 10px;
        }
        .ok {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #dc2626;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid #e5e5e5;
        }
        th {
            background: #f8f8f8;
            font-weight: 600;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .ad-preview {
            border: 2px dashed #ccc;
            padding: 15px;
            margin-top: 10px;
            min-height: 100px;
            background: #fafafa;
        }
    </style>
</head>
<body>
    <h1>🔍 広告表示デバッグツール</h1>
    
    <?php
    global $wpdb;
    
    // 1. ファイル存在確認
    echo '<div class="section">';
    echo '<h2>1. ファイル存在確認</h2>';
    
    $affiliate_file = get_template_directory() . '/inc/affiliate-ad-manager.php';
    $file_exists = file_exists($affiliate_file);
    
    echo '<p>ファイルパス: <code>' . esc_html($affiliate_file) . '</code></p>';
    echo '<p>存在: ' . ($file_exists ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>') . '</p>';
    echo '<p>ファイルサイズ: ' . ($file_exists ? filesize($affiliate_file) . ' bytes' : 'N/A') . '</p>';
    echo '</div>';
    
    // 2. 関数存在確認
    echo '<div class="section">';
    echo '<h2>2. 関数存在確認</h2>';
    
    $functions = array(
        'ji_display_ad' => 'メイン表示関数',
        'JI_Affiliate_Ad_Manager' => 'クラス'
    );
    
    echo '<table>';
    echo '<tr><th>関数/クラス名</th><th>説明</th><th>ステータス</th></tr>';
    
    foreach ($functions as $func => $desc) {
        if ($func === 'JI_Affiliate_Ad_Manager') {
            $exists = class_exists($func);
        } else {
            $exists = function_exists($func);
        }
        echo '<tr>';
        echo '<td><code>' . esc_html($func) . '</code></td>';
        echo '<td>' . esc_html($desc) . '</td>';
        echo '<td>' . ($exists ? '<span class="ok">✓ 存在</span>' : '<span class="error">✗ 不在</span>') . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // 3. データベーステーブル確認
    echo '<div class="section">';
    echo '<h2>3. データベーステーブル確認</h2>';
    
    $table_name = $wpdb->prefix . 'ji_affiliate_ads';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    echo '<p>テーブル名: <code>' . esc_html($table_name) . '</code></p>';
    echo '<p>存在: ' . ($table_exists ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>') . '</p>';
    
    if ($table_exists) {
        $ad_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo '<p>登録広告数: <strong>' . intval($ad_count) . '</strong> 件</p>';
        
        if ($ad_count > 0) {
            // 広告一覧を表示
            $ads = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
            
            echo '<h3>登録されている広告:</h3>';
            echo '<table>';
            echo '<tr><th>ID</th><th>タイトル</th><th>位置</th><th>ステータス</th><th>デバイス</th><th>開始日</th><th>終了日</th></tr>';
            
            foreach ($ads as $ad) {
                $status_class = $ad->status === 'active' ? 'ok' : 'error';
                echo '<tr>';
                echo '<td>' . intval($ad->id) . '</td>';
                echo '<td>' . esc_html($ad->title) . '</td>';
                echo '<td><code>' . esc_html($ad->positions) . '</code></td>';
                echo '<td><span class="' . $status_class . '">' . esc_html($ad->status) . '</span></td>';
                echo '<td>' . esc_html($ad->device_target) . '</td>';
                echo '<td>' . ($ad->start_date ?: '未設定') . '</td>';
                echo '<td>' . ($ad->end_date ?: '未設定') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">⚠️ 広告が1件も登録されていません！</p>';
            echo '<p>WordPress管理画面 → <strong>アフィリエイト広告</strong> → <strong>広告一覧</strong> から広告を作成してください。</p>';
        }
    }
    echo '</div>';
    
    // 4. 広告取得テスト
    if (function_exists('ji_display_ad') && $table_exists && $ad_count > 0) {
        echo '<div class="section">';
        echo '<h2>4. 広告取得テスト</h2>';
        
        $test_positions = array(
            'single_grant_sidebar_bottom',
            'single_grant_content',
            'archive_sidebar',
            'archive_content'
        );
        
        echo '<p>各表示位置で広告が取得できるかテストします。</p>';
        
        foreach ($test_positions as $position) {
            echo '<h3>位置: <code>' . esc_html($position) . '</code></h3>';
            
            if (class_exists('JI_Affiliate_Ad_Manager')) {
                $manager = new JI_Affiliate_Ad_Manager();
                $ad = $manager->get_ad_for_position($position, array('page_type' => 'test'));
                
                if ($ad) {
                    echo '<p class="ok">✓ 広告が見つかりました</p>';
                    echo '<p><strong>広告ID:</strong> ' . intval($ad->id) . '</p>';
                    echo '<p><strong>タイトル:</strong> ' . esc_html($ad->title) . '</p>';
                    echo '<p><strong>タイプ:</strong> ' . esc_html($ad->ad_type) . '</p>';
                } else {
                    echo '<p class="warning">⚠️ この位置に表示可能な広告が見つかりませんでした</p>';
                }
            }
        }
        echo '</div>';
        
        // 5. 実際の表示テスト
        echo '<div class="section">';
        echo '<h2>5. 実際の広告表示テスト</h2>';
        
        echo '<p>位置: <code>single_grant_sidebar_bottom</code></p>';
        echo '<div class="ad-preview">';
        
        ob_start();
        ji_display_ad('single_grant_sidebar_bottom', array('page_type' => 'single-grant'));
        $ad_html = ob_get_clean();
        
        if (!empty($ad_html)) {
            echo '<p class="ok">✓ 広告HTMLが生成されました</p>';
            echo '<h4>生成されたHTML:</h4>';
            echo '<pre>' . esc_html($ad_html) . '</pre>';
            echo '<h4>実際の表示:</h4>';
            echo $ad_html;
        } else {
            echo '<p class="error">✗ 広告HTMLが生成されませんでした</p>';
            echo '<p><strong>考えられる原因:</strong></p>';
            echo '<ul>';
            echo '<li>広告の表示位置設定が <code>single_grant_sidebar_bottom</code> になっていない</li>';
            echo '<li>広告のステータスが「無効」になっている</li>';
            echo '<li>広告の開始日・終了日が現在の日時と合っていない</li>';
            echo '<li>デバイスターゲティングが一致していない</li>';
            echo '</ul>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    // 6. 推奨アクション
    echo '<div class="section">';
    echo '<h2>6. 推奨アクション</h2>';
    
    if (!$table_exists) {
        echo '<p class="error">❌ データベーステーブルが存在しません</p>';
        echo '<p><strong>解決方法:</strong> WordPress管理画面にログインすると、自動的にテーブルが作成されます。</p>';
    } elseif ($ad_count == 0) {
        echo '<p class="error">❌ 広告が登録されていません</p>';
        echo '<p><strong>解決方法:</strong></p>';
        echo '<ol>';
        echo '<li>WordPress管理画面にログイン</li>';
        echo '<li>左メニューから「アフィリエイト広告」をクリック</li>';
        echo '<li>「新規追加」ボタンをクリック</li>';
        echo '<li>広告情報を入力:';
        echo '<ul>';
        echo '<li><strong>タイトル:</strong> 広告の名前（管理用）</li>';
        echo '<li><strong>広告タイプ:</strong> HTML/画像/スクリプト</li>';
        echo '<li><strong>コンテンツ:</strong> 広告のHTMLコードまたは画像</li>';
        echo '<li><strong>表示位置:</strong> <code>single_grant_sidebar_bottom</code> を選択</li>';
        echo '<li><strong>対象ページ:</strong> <code>single-grant</code> または空白（全ページ）</li>';
        echo '<li><strong>ステータス:</strong> 有効</li>';
        echo '<li><strong>デバイス:</strong> 全て</li>';
        echo '</ul>';
        echo '</li>';
        echo '<li>「保存」ボタンをクリック</li>';
        echo '</ol>';
    } else {
        echo '<p class="ok">✓ システムは正常に動作しています</p>';
        echo '<p>広告が表示されない場合は、以下を確認してください:</p>';
        echo '<ul>';
        echo '<li>広告の表示位置設定が正しいか（<code>single_grant_sidebar_bottom</code>）</li>';
        echo '<li>広告のステータスが「有効」になっているか</li>';
        echo '<li>ブラウザのキャッシュをクリアしたか</li>';
        echo '<li>サーバーキャッシュ（Cloudflare等）をクリアしたか</li>';
        echo '</ul>';
    }
    
    echo '</div>';
    
    // 7. デバッグ情報
    echo '<div class="section">';
    echo '<h2>7. システム情報</h2>';
    echo '<table>';
    echo '<tr><th>項目</th><th>値</th></tr>';
    echo '<tr><td>PHP Version</td><td>' . phpversion() . '</td></tr>';
    echo '<tr><td>WordPress Version</td><td>' . get_bloginfo('version') . '</td></tr>';
    echo '<tr><td>Theme</td><td>' . wp_get_theme()->get('Name') . ' (' . wp_get_theme()->get('Version') . ')</td></tr>';
    echo '<tr><td>Database Prefix</td><td><code>' . $wpdb->prefix . '</code></td></tr>';
    echo '<tr><td>Current Time</td><td>' . current_time('mysql') . '</td></tr>';
    echo '</table>';
    echo '</div>';
    ?>
    
    <div class="section" style="background: #fffbeb; border-left: 4px solid #f59e0b;">
        <p><strong>📝 このデバッグツールについて:</strong></p>
        <p>このファイルは広告システムの動作確認用です。問題が解決したら、セキュリティのため削除してください。</p>
        <p><code>rm /home/user/webapp/debug-ads.php</code></p>
    </div>
</body>
</html>
<?php
?>
