# 検証環境とちょっとした開発アプリ
## 運用方法
### 1. 検証環境
 - ソースコード管理はしない。
 - フォルダ名はpoc{No.}。

### 2. 開発
 - ソースコード管理を行う。
 - 開発対象
   - module開発
   - theme開発
   - コントリビュートモジュールの検証
 - 開発アプリ
   - poc.module
     - JmeterBootManager
     - Save without page redirect

## 運用状況
1. LayoutBuilderの検証
2. UserページでのLayoutBuilderの検証
  ### 作業進捗
  構成のエクスポートまで終了。
  ### 検証内容
  LayoutBuilderの機能を拡張するモジュールの調査。
  - layout_builder_modal
    ブロック等を作成する際のモーダル表示。これがないと編集がしにくいかも。
  - layout_builder_widget
    edit画面でレイアウト変更を可能にするモジュール。そこまで利便性を感じない。
  - layout_builder_customizer
    
  - Layout Builder Asymmetric Translation
    翻訳で使えそう。


## 検証&開発予定
 - Ace editor
 - コア 多言語「掲載」「フロントページに掲載」

## テスト