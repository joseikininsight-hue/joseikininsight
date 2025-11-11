#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ページのタイトルとメタディスクリプションをスクレイピングして分析
"""

import requests
from bs4 import BeautifulSoup
import pandas as pd
import time
import re

def scrape_page_seo(url):
    """URLからタイトルとメタディスクリプションを取得"""
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # タイトル取得
        title = ''
        title_tag = soup.find('title')
        if title_tag:
            title = title_tag.get_text().strip()
        
        # メタディスクリプション取得
        description = ''
        meta_desc = soup.find('meta', attrs={'name': 'description'})
        if not meta_desc:
            meta_desc = soup.find('meta', attrs={'property': 'og:description'})
        if meta_desc and meta_desc.get('content'):
            description = meta_desc.get('content').strip()
        
        # H1取得
        h1 = ''
        h1_tag = soup.find('h1')
        if h1_tag:
            h1 = h1_tag.get_text().strip()
        
        return {
            'url': url,
            'title': title,
            'description': description,
            'h1': h1,
            'has_teiki_text': '【毎日更新】' in title or '助成金インサイト' in title or '補助金インサイト' in title
        }
        
    except Exception as e:
        print(f"エラー: {url} - {str(e)}")
        return {
            'url': url,
            'title': f'Error: {str(e)}',
            'description': '',
            'h1': '',
            'has_teiki_text': False
        }

def main():
    """メイン処理"""
    print("="*80)
    print("ページSEO情報スクレイピング")
    print("="*80)
    
    # 高順位・低CTRページを読み込み
    df = pd.read_csv('high_rank_low_ctr_pages.csv', encoding='utf-8-sig')
    
    # 上位20ページをサンプルとして取得
    sample_urls = df.head(20)['上位のページ'].tolist()
    
    results = []
    
    print(f"\n{len(sample_urls)}ページのSEO情報を取得中...")
    
    for i, url in enumerate(sample_urls, 1):
        print(f"\n[{i}/{len(sample_urls)}] {url}")
        result = scrape_page_seo(url)
        results.append(result)
        
        # 表示
        print(f"タイトル: {result['title'][:100]}")
        print(f"定型文あり: {'はい' if result['has_teiki_text'] else 'いいえ'}")
        
        # レート制限
        time.sleep(1)
    
    # データフレームに変換
    results_df = pd.DataFrame(results)
    
    # CSVに保存
    results_df.to_csv('scraped_titles.csv', index=False, encoding='utf-8-sig')
    print(f"\n✓ scraped_titles.csv に保存しました")
    
    # 統計表示
    print("\n" + "="*80)
    print("【分析結果】")
    print("="*80)
    
    total = len(results_df)
    with_teiki = results_df['has_teiki_text'].sum()
    
    print(f"\n総ページ数: {total}")
    print(f"定型文あり: {with_teiki} ({with_teiki/total*100:.1f}%)")
    print(f"定型文なし: {total - with_teiki} ({(total-with_teiki)/total*100:.1f}%)")
    
    # 定型文ありのページを表示
    if with_teiki > 0:
        print(f"\n【定型文が含まれるページ】")
        teiki_pages = results_df[results_df['has_teiki_text'] == True]
        for idx, row in teiki_pages.iterrows():
            print(f"\n- {row['url']}")
            print(f"  タイトル: {row['title']}")

if __name__ == "__main__":
    main()
