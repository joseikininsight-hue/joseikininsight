#!/bin/bash
# SEO改善: アーカイブページのメタディスクリプション最適化スクリプト
# 「毎日更新」などの定型文を削除し、ページ固有の価値を伝える説明文に変更

set -e  # エラーで停止

THEME_DIR="/home/user/webapp"
BACKUP_DIR="${THEME_DIR}/backups/seo_fix_$(date +%Y%m%d_%H%M%S)"

echo "=================================="
echo "SEO改善スクリプト"
echo "アーカイブページの最適化"
echo "=================================="

# バックアップディレクトリ作成
mkdir -p "$BACKUP_DIR"
echo "✓ バックアップディレクトリ作成: $BACKUP_DIR"

# 対象ファイルリスト
FILES=(
    "taxonomy-grant_category.php"
    "taxonomy-grant_prefecture.php"
    "taxonomy-grant_municipality.php"
    "taxonomy-grant_purpose.php"
    "taxonomy-grant_tag.php"
    "archive-grant.php"
)

# バックアップ
echo ""
echo "📦 ファイルのバックアップ中..."
for file in "${FILES[@]}"; do
    if [ -f "${THEME_DIR}/${file}" ]; then
        cp "${THEME_DIR}/${file}" "${BACKUP_DIR}/"
        echo "  ✓ ${file}"
    else
        echo "  ⚠ ${file} が見つかりません"
    fi
done

echo ""
echo "✅ バックアップ完了: ${BACKUP_DIR}"
echo ""
echo "📝 次のステップ:"
echo "1. テーマファイルを手動で編集します"
echo "2. メタディスクリプションから定型文を削除します"
echo "3. ページ固有の説明文に置き換えます"
echo ""
echo "対象ファイル:"
for file in "${FILES[@]}"; do
    echo "  - ${THEME_DIR}/${file}"
done
echo ""
echo "=================================="
echo "スクリプト完了"
echo "=================================="
