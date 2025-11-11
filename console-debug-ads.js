/**
 * Console-based Ad Debug Tool
 * ブラウザのコンソールで広告表示をデバッグするツール
 * 
 * 使用方法:
 * 1. 補助金詳細ページ（single-grant.php）を開く
 * 2. F12キーで開発者ツールを開く
 * 3. Consoleタブを選択
 * 4. このファイルの内容をすべてコピー＆ペーストして Enter
 * 
 * または:
 * 本番環境にアップロード後、以下のように読み込み:
 * <script src="https://joseikin-insight.com/console-debug-ads.js"></script>
 */

(function() {
    'use strict';
    
    console.log('%c========================================', 'color: #0066ff; font-weight: bold; font-size: 16px;');
    console.log('%c🔍 広告表示デバッグツール', 'color: #0066ff; font-weight: bold; font-size: 20px;');
    console.log('%c========================================', 'color: #0066ff; font-weight: bold; font-size: 16px;');
    console.log('');
    
    const results = {
        checks: [],
        warnings: [],
        errors: [],
        info: []
    };
    
    // ヘルパー関数: ログ出力
    function logSection(title) {
        console.log('');
        console.log('%c' + title, 'background: #0066ff; color: white; padding: 5px 10px; font-weight: bold;');
        console.log('');
    }
    
    function logSuccess(message) {
        console.log('%c✓ ' + message, 'color: #10b981; font-weight: bold;');
        results.checks.push({ type: 'success', message });
    }
    
    function logError(message) {
        console.log('%c✗ ' + message, 'color: #dc2626; font-weight: bold;');
        results.errors.push(message);
    }
    
    function logWarning(message) {
        console.log('%c⚠ ' + message, 'color: #f59e0b; font-weight: bold;');
        results.warnings.push(message);
    }
    
    function logInfo(message, data) {
        console.log('%c→ ' + message, 'color: #666;', data || '');
        if (data) {
            results.info.push({ message, data });
        }
    }
    
    // 1. ページ情報確認
    logSection('1. ページ情報確認');
    
    const pageInfo = {
        url: window.location.href,
        title: document.title,
        pathname: window.location.pathname,
        postType: document.body.className.match(/post-type-(\w+)/)?.[1] || '不明'
    };
    
    logInfo('現在のURL', pageInfo.url);
    logInfo('ページタイトル', pageInfo.title);
    logInfo('投稿タイプ', pageInfo.postType);
    
    if (pageInfo.postType === 'grant' || pageInfo.pathname.includes('/grants/')) {
        logSuccess('補助金詳細ページです');
    } else {
        logWarning('補助金詳細ページではない可能性があります');
    }
    
    // 2. サイドバー確認
    logSection('2. サイドバー構造確認');
    
    const sidebar = document.querySelector('.gus-sidebar');
    if (sidebar) {
        logSuccess('サイドバー要素が見つかりました');
        
        const sidebarSections = sidebar.querySelectorAll('.gus-sidebar-card, .sidebar-ad-space');
        logInfo('サイドバー内のセクション数', sidebarSections.length + '個');
        
        console.group('サイドバー構造:');
        sidebarSections.forEach((section, index) => {
            const title = section.querySelector('.gus-sidebar-title, h2');
            const titleText = title ? title.textContent.trim() : 'タイトルなし';
            const isAdSpace = section.classList.contains('sidebar-ad-space');
            
            if (isAdSpace) {
                console.log(`  ${index + 1}. %c[広告スペース]`, 'color: #f59e0b; font-weight: bold;');
            } else {
                console.log(`  ${index + 1}. ${titleText}`);
            }
        });
        console.groupEnd();
    } else {
        logError('サイドバー要素が見つかりません');
    }
    
    // 3. 広告スペース要素確認
    logSection('3. 広告スペース要素確認');
    
    const adSpaces = document.querySelectorAll('.sidebar-ad-space');
    const adSpaceBottom = document.querySelector('.sidebar-ad-space.sidebar-ad-bottom');
    
    if (adSpaces.length > 0) {
        logSuccess(`広告スペース要素が ${adSpaces.length} 個見つかりました`);
        
        console.group('広告スペース詳細:');
        adSpaces.forEach((space, index) => {
            console.log(`  ${index + 1}. クラス: ${space.className}`);
            console.log(`     HTML: ${space.innerHTML.trim() || '(空)'}`);
            console.log(`     表示: ${window.getComputedStyle(space).display}`);
            console.log(`     可視: ${window.getComputedStyle(space).visibility}`);
        });
        console.groupEnd();
    } else {
        logError('広告スペース要素が見つかりません');
    }
    
    if (adSpaceBottom) {
        logSuccess('サイドバー下部の広告スペースが見つかりました');
        logInfo('HTML内容', adSpaceBottom.innerHTML.trim() || '(空)');
        
        if (adSpaceBottom.innerHTML.trim() === '') {
            logWarning('広告スペースは存在しますが、中身が空です');
            results.warnings.push('広告HTMLが生成されていない可能性があります');
        }
    } else {
        logError('サイドバー下部の広告スペース(.sidebar-ad-bottom)が見つかりません');
    }
    
    // 4. 広告要素確認
    logSection('4. 広告要素確認');
    
    const adElements = document.querySelectorAll('.ji-affiliate-ad');
    
    if (adElements.length > 0) {
        logSuccess(`広告要素(.ji-affiliate-ad)が ${adElements.length} 個見つかりました`);
        
        console.group('広告要素詳細:');
        adElements.forEach((ad, index) => {
            const adId = ad.getAttribute('data-ad-id');
            const position = ad.getAttribute('data-position');
            const pageUrl = ad.getAttribute('data-page-url');
            const categoryId = ad.getAttribute('data-category-id');
            
            console.log(`%c広告 ${index + 1}:`, 'font-weight: bold;');
            console.log(`  広告ID: ${adId}`);
            console.log(`  位置: ${position}`);
            console.log(`  ページURL: ${pageUrl}`);
            console.log(`  カテゴリーID: ${categoryId}`);
            console.log(`  HTML:`, ad.innerHTML.substring(0, 200) + '...');
            console.log(`  表示: ${window.getComputedStyle(ad).display}`);
            console.log(`  可視: ${window.getComputedStyle(ad).visibility}`);
            
            // CSS確認
            const computedStyle = window.getComputedStyle(ad);
            const displayIssues = [];
            
            if (computedStyle.display === 'none') {
                displayIssues.push('display: none が設定されています');
            }
            if (computedStyle.visibility === 'hidden') {
                displayIssues.push('visibility: hidden が設定されています');
            }
            if (computedStyle.opacity === '0') {
                displayIssues.push('opacity: 0 が設定されています');
            }
            if (parseFloat(computedStyle.width) === 0) {
                displayIssues.push('width が 0 です');
            }
            if (parseFloat(computedStyle.height) === 0) {
                displayIssues.push('height が 0 です');
            }
            
            if (displayIssues.length > 0) {
                console.log(`  %c⚠ 表示問題:`, 'color: #f59e0b;');
                displayIssues.forEach(issue => console.log(`    - ${issue}`));
            } else {
                console.log(`  %c✓ CSS表示問題なし`, 'color: #10b981;');
            }
        });
        console.groupEnd();
    } else {
        logError('広告要素(.ji-affiliate-ad)が見つかりません');
        logWarning('PHPで広告HTMLが生成されていない可能性があります');
    }
    
    // 5. Ajax確認
    logSection('5. Ajax / JavaScript確認');
    
    // jQueryの存在確認
    if (typeof jQuery !== 'undefined') {
        logSuccess('jQuery が読み込まれています (バージョン: ' + jQuery.fn.jquery + ')');
    } else {
        logError('jQuery が読み込まれていません');
    }
    
    // Ajax URLの確認
    const ajaxUrl = document.querySelector('script')?.textContent.match(/admin-ajax\.php/);
    if (ajaxUrl) {
        logSuccess('admin-ajax.php への参照が見つかりました');
    } else {
        logWarning('admin-ajax.php への参照が見つかりませんでした');
    }
    
    // 6. ネットワークリクエスト確認（手動確認用ガイド）
    logSection('6. ネットワークリクエスト確認（手動確認）');
    
    console.log('%c以下を手動で確認してください:', 'font-weight: bold;');
    console.log('1. Network タブを開く');
    console.log('2. フィルターを "ajax" に設定');
    console.log('3. 以下のリクエストがあるか確認:');
    console.log('   - ji_track_ad_impression');
    console.log('   - ji_track_ad_click');
    console.log('');
    console.log('これらのリクエストがない場合、PHPで広告が取得できていない可能性があります');
    
    // 7. PHP関数確認（HTMLコメント確認）
    logSection('7. PHP関数実行確認');
    
    const htmlSource = document.documentElement.outerHTML;
    
    // function_exists のチェック
    if (htmlSource.includes('function_exists')) {
        logWarning('HTMLソースに function_exists が残っています（PHPエラーの可能性）');
    } else {
        logSuccess('function_exists のチェックは通過しています');
    }
    
    // エラーメッセージの確認
    const phpErrors = [
        'Fatal error',
        'Warning:',
        'Notice:',
        'Parse error',
        'Call to undefined function'
    ];
    
    let errorFound = false;
    phpErrors.forEach(errorType => {
        if (htmlSource.includes(errorType)) {
            logError(`PHPエラーが検出されました: ${errorType}`);
            errorFound = true;
        }
    });
    
    if (!errorFound) {
        logSuccess('PHPエラーは検出されませんでした');
    }
    
    // 8. データベース接続確認用
    logSection('8. データベース診断（推奨アクション）');
    
    console.log('%c以下のURLにアクセスしてデータベースを確認してください:', 'font-weight: bold;');
    console.log('%chttps://joseikin-insight.com/debug-ads.php', 'color: #0066ff; font-size: 16px; font-weight: bold;');
    console.log('');
    console.log('このツールで確認できること:');
    console.log('  - データベースに広告が登録されているか');
    console.log('  - 広告の設定が正しいか');
    console.log('  - 広告が取得できるか');
    
    // 9. 総合診断結果
    logSection('9. 総合診断結果');
    
    console.log('');
    console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #0066ff;');
    
    if (results.errors.length === 0) {
        console.log('%c✓ フロントエンドに重大な問題は検出されませんでした', 'color: #10b981; font-size: 16px; font-weight: bold;');
    } else {
        console.log('%c✗ 以下の問題が検出されました:', 'color: #dc2626; font-size: 16px; font-weight: bold;');
        results.errors.forEach((error, index) => {
            console.log(`  ${index + 1}. ${error}`);
        });
    }
    
    if (results.warnings.length > 0) {
        console.log('');
        console.log('%c⚠ 警告:', 'color: #f59e0b; font-size: 14px; font-weight: bold;');
        results.warnings.forEach((warning, index) => {
            console.log(`  ${index + 1}. ${warning}`);
        });
    }
    
    console.log('');
    console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: #0066ff;');
    console.log('');
    
    // 10. 推奨アクション
    logSection('10. 推奨アクション');
    
    if (adElements.length === 0 && adSpaces.length > 0) {
        console.log('%c最も可能性が高い原因: データベースに広告が未登録', 'color: #dc2626; font-weight: bold; font-size: 14px;');
        console.log('');
        console.log('解決方法:');
        console.log('1. WordPress管理画面にログイン');
        console.log('2. アフィリエイト広告 → 広告一覧 → 新規追加');
        console.log('3. 以下の設定で広告を作成:');
        console.log('   - タイトル: サイドバー広告1');
        console.log('   - 表示位置: single_grant_sidebar_bottom ✓');
        console.log('   - ステータス: 有効 ✓');
        console.log('   - デバイス: 全て ✓');
        console.log('4. 保存');
        console.log('');
        console.log('または、以下のURLで詳細診断を実行:');
        console.log('%chttps://joseikin-insight.com/debug-ads.php', 'color: #0066ff; font-size: 14px; font-weight: bold;');
    } else if (adElements.length > 0) {
        console.log('%c広告要素は存在します', 'color: #10b981; font-weight: bold;');
        console.log('');
        console.log('表示されない場合は以下を確認:');
        console.log('1. ブラウザキャッシュをクリア (Shift + F5)');
        console.log('2. Cloudflare キャッシュをクリア');
        console.log('3. AdBlock等の広告ブロッカーを無効化');
        console.log('4. CSS で display: none が設定されていないか確認（上記の詳細参照）');
    }
    
    // 11. デバッグオブジェクトをグローバルに保存
    window.adDebugResults = results;
    
    console.log('');
    console.log('%c診断結果をグローバル変数に保存しました:', 'color: #0066ff; font-weight: bold;');
    console.log('確認方法: %cwindow.adDebugResults', 'background: #f0f0f0; padding: 2px 5px; font-family: monospace;');
    console.log('');
    
    console.log('%c========================================', 'color: #0066ff; font-weight: bold; font-size: 16px;');
    console.log('%c診断完了！', 'color: #0066ff; font-weight: bold; font-size: 20px;');
    console.log('%c========================================', 'color: #0066ff; font-weight: bold; font-size: 16px;');
    console.log('');
    
    // 簡易レポート
    console.log('%c簡易レポート:', 'background: #0066ff; color: white; padding: 5px 10px; font-weight: bold;');
    console.table({
        '広告スペース要素': adSpaces.length > 0 ? '✓ 存在' : '✗ なし',
        '広告要素(.ji-affiliate-ad)': adElements.length > 0 ? `✓ ${adElements.length}個` : '✗ なし',
        'jQuery': typeof jQuery !== 'undefined' ? '✓ あり' : '✗ なし',
        'PHPエラー': errorFound ? '✗ あり' : '✓ なし',
        'サイドバー': sidebar ? '✓ あり' : '✗ なし'
    });
    
})();
