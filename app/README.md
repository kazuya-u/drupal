# Template for Drupal

## 環境構築手順
### 1. Githubからソースコードを取得。
```bash
git clone https://github.com/kazuya-u/Drupal-Template.git
```

### 2. Packageの取得。
```bash
cd drupal-template/
```
```bash
composer install
```

### 3. 各コンテナをビルド（Lando）
```bash
lando start
```
