# コンソールデバッグツール - 使用方法
**作成日**: 2025-11-11  
**ファイル**: console-debug-ads.js  
**目的**: ブラウザのコンソールで広告表示を即座にデバッグ

---

## 🎯 使用方法（3つの方法）

### 方法1: コピー＆ペースト（最も簡単）⭐

1. **補助金詳細ページを開く**
   ```
   例: https://joseikin-insight.com/grants/任意の補助金/
   ```

2. **開発者ツールを開く**
   - Windows: `F12` または `Ctrl + Shift + I`
   - Mac: `Cmd + Option + I`

3. **Consoleタブを選択**

4. **以下のコマンドを実行**
   ```javascript
   // 方法A: スクリプトを動的に読み込み
   var script = document.createElement('script');
   script.src = 'https://joseikin-insight.com/console-debug-ads.js?' + Date.now();
   document.head.appendChild(script);
   ```
   
   または
   
   ```javascript
   // 方法B: ファイルの中身を直接ペースト
   // console-debug-ads.js の内容をすべてコピーしてペースト
   ```

5. **Enterキーを押す**

6. **診断結果が表示されます**

---

### 方法2: ブックマークレット（繰り返し使用に便利）

1. **ブックマークバーを右クリック → 「ページを追加」**

2. **名前**: `広告デバッグ`

3. **URL**:
   ```javascript
   javascript:(function(){var s=document.createElement('script');s.src='https://joseikin-insight.com/console-debug-ads.js?'+Date.now();document.head.appendChild(s);})();
   ```

4. **保存**

5. **使用時**: 補助金詳細ページでブックマークをクリック

---

### 方法3: テーマに組み込み（常時有効化）

#### functions.php に追加（管理者のみ）

```php
/**
 * 管理者用: 広告デバッグスクリプト読み込み
 * 本番環境では管理者のみに表示
 */
function load_ad_debug_script() {
    // 管理者かつ補助金詳細ページのみ
    if (current_user_can('administrator') && is_singular('grant')) {
        wp_enqueue_script(
            'console-debug-ads',
            get_template_directory_uri() . '/console-debug-ads.js',
            array(),
            time(), // キャッシュ無効化
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'load_ad_debug_script');
```

#### single-grant.php に直接追加（全ユーザー向け）

**注意**: 本番環境では使用しないでください（デバッグ情報が公開されます）

```php
<?php if (current_user_can('administrator')): ?>
<script src="<?php echo get_template_directory_uri(); ?>/console-debug-ads.js?v=<?php echo time(); ?>"></script>
<?php endif; ?>
```

---

## 📊 診断項目

### 1. ページ情報確認
- 現在のURL
- ページタイトル
- 投稿タイプ（grant かどうか）

### 2. サイドバー構造確認
- サイドバー要素の存在
- サイドバー内のセクション一覧
- 広告スペースの位置

### 3. 広告スペース要素確認
- `.sidebar-ad-space` 要素の存在
- `.sidebar-ad-bottom` 要素の存在
- HTML内容（空かどうか）

### 4. 広告要素確認
- `.ji-affiliate-ad` 要素の存在
- 広告ID、位置、カテゴリー情報
- CSS表示問題（display: none 等）

### 5. Ajax / JavaScript確認
- jQuery の読み込み状況
- admin-ajax.php への参照

### 6. ネットワークリクエスト確認
- インプレッショントラッキング
- クリックトラッキング

### 7. PHP関数実行確認
- PHPエラーの検出
- function_exists の通過確認

### 8. データベース診断
- debug-ads.php へのリンク

### 9. 総合診断結果
- 検出された問題の一覧
- 警告の一覧

### 10. 推奨アクション
- 最も可能性が高い原因
- 具体的な解決方法

---

## 🎨 出力例

```
========================================
🔍 広告表示デバッグツール
========================================

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1. ページ情報確認
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

→ 現在のURL https://joseikin-insight.com/grants/example/
→ ページタイトル 補助金タイトル例
→ 投稿タイプ grant
✓ 補助金詳細ページです

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
2. サイドバー構造確認
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ サイドバー要素が見つかりました
→ サイドバー内のセクション数 8個

サイドバー構造:
  1. 統計情報
  2. 目次
  3. 関連補助金
  4. 詳しい記事
  5. アクション
  6. (AIチャット)
  7. タグ
  8. [広告スペース]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
3. 広告スペース要素確認
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ 広告スペース要素が 1 個見つかりました
✓ サイドバー下部の広告スペースが見つかりました
⚠ 広告スペースは存在しますが、中身が空です

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
4. 広告要素確認
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✗ 広告要素(.ji-affiliate-ad)が見つかりません
⚠ PHPで広告HTMLが生成されていない可能性があります

...
```

---

## 💡 診断結果の解釈

### ケース1: 広告スペースは存在するが中身が空
```
✓ 広告スペース要素が 1 個見つかりました
⚠ 広告スペースは存在しますが、中身が空です
✗ 広告要素(.ji-affiliate-ad)が見つかりません
```

**原因**: データベースに広告が未登録、または設定が間違っている

**解決方法**:
1. https://joseikin-insight.com/debug-ads.php で詳細診断
2. WordPress管理画面で広告を新規作成

---

### ケース2: 広告要素は存在するが表示されない
```
✓ 広告要素(.ji-affiliate-ad)が 1 個見つかりました
⚠ 表示問題:
    - display: none が設定されています
```

**原因**: CSS で非表示になっている

**解決方法**:
1. AdBlock等の広告ブロッカーを無効化
2. テーマのCSSを確認
3. Elementsタブで該当要素のCSSを確認

---

### ケース3: すべて正常だがブラウザで見えない
```
✓ 広告要素が見つかりました
✓ CSS表示問題なし
```

**原因**: キャッシュ問題

**解決方法**:
1. ブラウザキャッシュクリア（Shift + F5）
2. Cloudflare キャッシュクリア
3. WordPressキャッシュクリア

---

## 🔍 追加の手動確認方法

### Networkタブでの確認

1. **Networkタブを開く**
2. **フィルターを "ajax" に設定**
3. **ページをリロード**
4. **以下のリクエストがあるか確認**:
   - `action=ji_track_ad_impression`
   - `action=ji_track_ad_click`（広告クリック時）

**これらがない場合**: PHPで広告が取得できていない

---

### Elementsタブでの確認

1. **Elementsタブを開く**
2. **Ctrl+F（検索）で以下を検索**:
   - `sidebar-ad-space`
   - `ji-affiliate-ad`
   - `data-ad-id`

**見つからない場合**: HTMLが生成されていない

---

## 🛠️ グローバル変数

診断結果は `window.adDebugResults` に保存されます。

### 確認方法

```javascript
// すべての結果
console.log(window.adDebugResults);

// エラーのみ
console.log(window.adDebugResults.errors);

// 警告のみ
console.log(window.adDebugResults.warnings);

// 成功チェック
console.log(window.adDebugResults.checks);

// 情報
console.log(window.adDebugResults.info);
```

---

## 📝 コミット方法

```bash
cd /home/user/webapp

# ファイルを追加
git add console-debug-ads.js CONSOLE_DEBUG_USAGE.md

# コミット
git commit -m "debug: コンソールベース広告デバッグツール追加

ブラウザのコンソールで即座に診断可能:
- 10項目の包括的診断
- カラー出力で見やすい
- 推奨アクションを自動表示
- ブックマークレット対応

使用方法:
1. F12でコンソールを開く
2. スクリプトを読み込み
3. 診断結果を確認"

# プッシュ
git push origin main
```

---

## ⚠️ 注意事項

### 本番環境での使用

- ✅ 管理者のみが実行する分には問題なし
- ❌ 全ユーザーに表示するのは避ける（デバッグ情報の公開）
- ✅ 問題解決後はスクリプトの読み込みを無効化

### セキュリティ

- このスクリプトは読み取り専用
- データベースやファイルを変更しない
- フロントエンドのHTML/CSSのみを確認

---

## 📞 サポート

### 問題が解決しない場合

1. **コンソールデバッグツールの出力をコピー**
2. **debug-ads.php の結果をスクリーンショット**
3. **以下の情報を提供**:
   - ブラウザの種類とバージョン
   - AdBlock等の拡張機能の有無
   - エラーメッセージ

---

## 🎓 まとめ

### このツールでわかること

- ✅ 広告スペースが存在するか
- ✅ 広告HTMLが生成されているか
- ✅ CSSで非表示になっていないか
- ✅ JavaScriptエラーがないか
- ✅ PHPエラーがないか

### このツールでわからないこと

- ❌ データベースの内容（→ debug-ads.php を使用）
- ❌ サーバー側のPHPエラー（→ エラーログを確認）
- ❌ WordPress の設定（→ 管理画面を確認）

---

**作成者**: Claude AI  
**作成日時**: 2025-11-11  
**ファイル**: console-debug-ads.js (12.4KB)  
**ドキュメント**: CONSOLE_DEBUG_USAGE.md (このファイル)
