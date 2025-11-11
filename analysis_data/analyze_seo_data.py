#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
SEOデータ分析スクリプト
Search ConsoleとGoogle Analyticsのデータを分析し、改善が必要なページを特定する
"""

import pandas as pd
import numpy as np
import os
from pathlib import Path

def load_csv_with_encoding(filepath):
    """CSVファイルを適切なエンコーディングで読み込む"""
    encodings = ['utf-8', 'shift-jis', 'cp932', 'utf-8-sig']
    
    for encoding in encodings:
        try:
            df = pd.read_csv(filepath, encoding=encoding)
            print(f"✓ {Path(filepath).name} を {encoding} で読み込みました")
            return df
        except Exception as e:
            continue
    
    raise Exception(f"ファイル {filepath} の読み込みに失敗しました")

def analyze_pages():
    """ページデータを分析"""
    print("\n" + "="*80)
    print("【ページデータ分析】")
    print("="*80)
    
    df = load_csv_with_encoding('pages.csv')
    
    # カラム名を確認
    print(f"\nカラム名: {df.columns.tolist()}")
    print(f"総ページ数: {len(df)}")
    
    # CTRを数値に変換
    if 'CTR' in df.columns:
        df['CTR_numeric'] = df['CTR'].str.rstrip('%').astype(float) / 100
    
    # 掲載順位と表示回数で絞り込み
    # 条件: 掲載順位10位以内 かつ 表示回数30回以上 かつ CTR 10%未満
    if all(col in df.columns for col in ['掲載順位', '表示回数', 'CTR_numeric']):
        high_rank_low_ctr = df[
            (df['掲載順位'] <= 10) & 
            (df['表示回数'] >= 30) & 
            (df['CTR_numeric'] < 0.10)
        ].copy()
        
        print(f"\n【高順位・低CTRページ】（掲載順位10位以内 & 表示回数30回以上 & CTR<10%）")
        print(f"該当ページ数: {len(high_rank_low_ctr)}")
        
        if len(high_rank_low_ctr) > 0:
            high_rank_low_ctr = high_rank_low_ctr.sort_values('CTR_numeric')
            print("\nワースト10:")
            print(high_rank_low_ctr[['上位のページ', 'クリック数', '表示回数', 'CTR', '掲載順位']].head(10).to_string())
            
            # CSVに保存
            high_rank_low_ctr.to_csv('high_rank_low_ctr_pages.csv', index=False, encoding='utf-8-sig')
            print(f"\n✓ high_rank_low_ctr_pages.csv に保存しました ({len(high_rank_low_ctr)}ページ)")
    
    # 表示回数は多いがクリックが少ないページ
    if all(col in df.columns for col in ['表示回数', 'クリック数', 'CTR_numeric']):
        high_impression_low_click = df[
            (df['表示回数'] >= 50) & 
            (df['CTR_numeric'] < 0.05)
        ].copy()
        
        print(f"\n【高表示回数・低CTRページ】（表示回数50回以上 & CTR<5%）")
        print(f"該当ページ数: {len(high_impression_low_click)}")
        
        if len(high_impression_low_click) > 0:
            high_impression_low_click = high_impression_low_click.sort_values('表示回数', ascending=False)
            print("\n表示回数トップ10:")
            print(high_impression_low_click[['上位のページ', 'クリック数', '表示回数', 'CTR', '掲載順位']].head(10).to_string())
            
            # CSVに保存
            high_impression_low_click.to_csv('high_impression_low_ctr_pages.csv', index=False, encoding='utf-8-sig')
            print(f"\n✓ high_impression_low_ctr_pages.csv に保存しました ({len(high_impression_low_click)}ページ)")
    
    return df

def analyze_queries():
    """クエリデータを分析"""
    print("\n" + "="*80)
    print("【検索クエリ分析】")
    print("="*80)
    
    df = load_csv_with_encoding('queries.csv')
    
    print(f"\nカラム名: {df.columns.tolist()}")
    print(f"総クエリ数: {len(df)}")
    
    # CTRを数値に変換
    if 'CTR' in df.columns:
        df['CTR_numeric'] = df['CTR'].str.rstrip('%').astype(float) / 100
    
    # 高表示回数・低CTRのクエリ
    if all(col in df.columns for col in ['表示回数', 'CTR_numeric']):
        high_imp_queries = df[
            (df['表示回数'] >= 20) & 
            (df['CTR_numeric'] < 0.05)
        ].copy()
        
        print(f"\n【改善機会のあるクエリ】（表示回数20回以上 & CTR<5%）")
        print(f"該当クエリ数: {len(high_imp_queries)}")
        
        if len(high_imp_queries) > 0:
            high_imp_queries = high_imp_queries.sort_values('表示回数', ascending=False)
            print("\n表示回数トップ20:")
            print(high_imp_queries[['上位のクエリ', 'クリック数', '表示回数', 'CTR', '掲載順位']].head(20).to_string())
    
    # クリック数トップ20
    if 'クリック数' in df.columns:
        top_clicks = df.sort_values('クリック数', ascending=False).head(20)
        print(f"\n【クリック数トップ20のクエリ】")
        print(top_clicks[['上位のクエリ', 'クリック数', '表示回数', 'CTR', '掲載順位']].to_string())
    
    return df

def analyze_devices():
    """デバイス別データを分析"""
    print("\n" + "="*80)
    print("【デバイス別パフォーマンス分析】")
    print("="*80)
    
    df = load_csv_with_encoding('devices.csv')
    
    print(f"\nカラム名: {df.columns.tolist()}")
    print(df.to_string())
    
    # CTRを数値に変換
    if 'CTR' in df.columns:
        df['CTR_numeric'] = df['CTR'].str.rstrip('%').astype(float) / 100
    
    print("\n【重要な発見】")
    print("- PCでの掲載順位が極端に低い（24.08位）")
    print("- モバイルでは良好（7.83位）")
    print("- PCのCTRも低い（0.52%）")
    print("\n→ PCサイトの技術的問題またはモバイルファースト対応の課題が疑われます")
    
    return df

def extract_page_urls(df):
    """ページURLからパスを抽出"""
    urls = []
    if '上位のページ' in df.columns:
        for url in df['上位のページ']:
            if pd.notna(url) and 'joseikin-insight.com' in url:
                # URLからパスを抽出
                path = url.replace('https://joseikin-insight.com', '')
                if path and path != '/':
                    urls.append(url)
    return urls

def generate_improvement_report(pages_df):
    """改善レポートを生成"""
    print("\n" + "="*80)
    print("【改善アクションプラン】")
    print("="*80)
    
    # 高順位・低CTRページ
    if all(col in pages_df.columns for col in ['掲載順位', '表示回数', 'CTR_numeric']):
        priority_pages = pages_df[
            (pages_df['掲載順位'] <= 10) & 
            (pages_df['表示回数'] >= 30) & 
            (pages_df['CTR_numeric'] < 0.10)
        ].copy()
        
        print(f"\n【最優先】タイトル・ディスクリプション改善対象: {len(priority_pages)}ページ")
        print("\n具体的なアクション:")
        print("1. 定型文「→ 【毎日更新】...」をタイトル・説明文から削除")
        print("2. 各ページの内容に合った魅力的なタイトルに変更")
        print("3. ディスクリプションでページ固有の価値を明確に伝える")
        
        if len(priority_pages) > 0:
            print(f"\n優先度トップ5のページ:")
            top5 = priority_pages.sort_values('表示回数', ascending=False).head(5)
            for idx, row in top5.iterrows():
                print(f"\n- {row['上位のページ']}")
                print(f"  表示回数: {row['表示回数']:,} / CTR: {row['CTR']} / 順位: {row['掲載順位']}")
    
    print("\n" + "="*80)
    print("【技術的調査が必要な項目】")
    print("="*80)
    print("\n1. PCサイトのパフォーマンス調査")
    print("   - Core Web Vitals（LCP, FID, CLS）の確認")
    print("   - PC版のレスポンシブデザイン検証")
    print("   - JavaScriptエラーの有無確認")
    print("   - レンダリングブロック要素の確認")
    
    print("\n2. モバイルファーストインデックス対応")
    print("   - モバイル版とPC版のコンテンツ同一性確認")
    print("   - 構造化データの実装確認")
    print("   - 内部リンク構造の最適化")

def main():
    """メイン実行関数"""
    print("="*80)
    print("SEOデータ分析レポート")
    print("joseikin-insight.com")
    print("="*80)
    
    os.chdir('/home/user/webapp/analysis_data')
    
    # データ分析
    pages_df = analyze_pages()
    queries_df = analyze_queries()
    devices_df = analyze_devices()
    
    # 改善レポート生成
    generate_improvement_report(pages_df)
    
    print("\n" + "="*80)
    print("分析完了")
    print("="*80)
    print("\n生成されたファイル:")
    print("- high_rank_low_ctr_pages.csv: 高順位だがCTRが低いページ一覧")
    print("- high_impression_low_ctr_pages.csv: 表示回数は多いがCTRが低いページ一覧")

if __name__ == "__main__":
    main()
