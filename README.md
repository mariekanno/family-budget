# Family Budget — MVP

Laravel 10 / MySQL 8.4 の既存マイグレーションを使用する家族向け家計簿。

## ローカル起動（既存 Sail 環境）
既存のローカル変更をコミットまたは退避してから、このブランチを取得してください。既存 .env は上書きしないでください。

```sh
git fetch origin
git switch feature/family-budget-mvp
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

初回のみ依存関係のインストール、.env の作成と APP_KEY の生成が必要です。APP_PORT の設定に合わせ localhost を開いてください。npm のビルドは不要です。

## 機能
登録・ログイン、家族作成、メールアドレス指定の招待コード、共有・非公開の収支 CRUD、月別家族収支、自分の個人収支、カテゴリ別支出・予算、CSV 入出力。招待コードは手動送付。明細編集削除は登録者のみ、予算と招待は管理者のみ。

## 制限
固定費の自動登録、パスワード再設定、メール認証、カテゴリ編集、CSVの列マッピング・行単位重複検出は未実装。CSVは出力と同じ固定列。出力には閲覧可能な個人明細を含むため共有時に注意。実行環境でのテスト・セキュリティレビュー未完了。Laravel 10 はサポート終了済みのため、公開前にサポート中のバージョンへの更新、HTTPS、APP_DEBUG=false、バックアップ、認証の強化が必要。本番データを投入する前に検証すること。

```sh
./vendor/bin/sail artisan test
```
