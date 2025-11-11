<?php
/**
 * カテゴリー説明文の一括更新スクリプト
 * 「【毎日更新】」などの定型文を削除し、カテゴリー固有の説明文に変更
 * 
 * 使用方法:
 * php fix_category_descriptions.php
 */

// WordPress環境の読み込み
require_once(__DIR__ . '/../../../wp-load.php');

if (!defined('ABSPATH')) {
    die('WordPress環境を読み込めませんでした');
}

echo "==================================\n";
echo "カテゴリー説明文 一括更新ツール\n";
echo "==================================\n\n";

// 削除する定型文のパターン
$patterns_to_remove = [
    '/【毎日更新】/',
    '/助成金インサイトが、国・自治の公募情報を速報でお届けします。/',
    '/地域やカテゴリでの絞り込みも可能。/',
    '/日本全国の補助金・助成金・給付金の最新情報一覧。/',
];

// grant_categoryタクソノミーの全タームを取得
$terms = get_terms([
    'taxonomy' => 'grant_category',
    'hide_empty' => false,
]);

if (is_wp_error($terms)) {
    die("エラー: タームの取得に失敗しました - " . $terms->get_error_message() . "\n");
}

$total = count($terms);
$updated = 0;
$unchanged = 0;

echo "対象カテゴリー数: {$total}\n\n";

foreach ($terms as $term) {
    $original_description = $term->description;
    $new_description = $original_description;
    
    // 定型文を削除
    foreach ($patterns_to_remove as $pattern) {
        $new_description = preg_replace($pattern, '', $new_description);
    }
    
    // 余分なスペースや改行を整理
    $new_description = trim(preg_replace('/\s+/', ' ', $new_description));
    
    // 変更がある場合のみ更新
    if ($original_description !== $new_description) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "カテゴリー: {$term->name} (ID: {$term->term_id})\n";
        echo "件数: {$term->count}件\n";
        echo "\n【変更前】\n";
        echo wordwrap($original_description, 70) . "\n";
        echo "\n【変更後】\n";
        echo wordwrap($new_description, 70) . "\n";
        
        // 更新を実行
        $result = wp_update_term($term->term_id, 'grant_category', [
            'description' => $new_description
        ]);
        
        if (is_wp_error($result)) {
            echo "\n❌ エラー: " . $result->get_error_message() . "\n";
        } else {
            echo "\n✅ 更新成功\n";
            $updated++;
        }
    } else {
        $unchanged++;
        echo "カテゴリー: {$term->name} - 変更なし\n";
    }
}

echo "\n==================================\n";
echo "処理完了\n";
echo "==================================\n";
echo "総カテゴリー数: {$total}\n";
echo "更新: {$updated}件\n";
echo "変更なし: {$unchanged}件\n";
echo "==================================\n";

// 他のタクソノミーも同様に処理
echo "\n\n他のタクソノミーも処理しますか？\n";
echo "対象: grant_prefecture, grant_municipality, grant_purpose, grant_tag\n";
echo "\n続行する場合は、各タクソノミー用のスクリプトを実行してください。\n";
