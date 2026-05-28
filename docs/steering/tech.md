# Technology Stack

## Architecture

モノリシック Laravel アプリケーション。HTTP は `routes/web.php`（および将来の `routes/api.php`）、ドメインロジックは `app/` 配下、プレゼンテーションは `resources/views` + Vite ビルドアセット。開発は Laravel Sail（Docker）上で実行する。

## Core Technologies

- **Language**: PHP 8.3+（Sail ランタイム 8.5）
- **Framework**: Laravel 13
- **Frontend build**: Vite 8、Tailwind CSS v4（`@tailwindcss/vite`）
- **Package managers**: Composer（PHP）、Bun（JS、`bun.lock`）
- **Runtime (dev)**: Laravel Sail（`compose.yaml`、PHP 8.5 イメージ）

## Key Libraries

| 領域 | ライブラリ | 用途 |
|------|-----------|------|
| 開発体験 | laravel/boost | AI ガイドライン、MCP、スキル同期 |
| コンテナ | laravel/sail | ローカル Docker 開発環境 |
| 品質 | laravel/pint | PHP コードスタイル |
| テスト | phpunit/phpunit 12 | Feature / Unit テスト |
| ログ | laravel/pail | 開発時ログ tail |

## Development Standards

### PHP

- コンストラクタプロパティプロモーション、明示的な戻り値型・引数型
- Eloquent 属性は PHP 8 属性（例: `#[Fillable]`, `#[Hidden]`）を優先
- 制御構造は常に波括弧を使用

### コード品質

- 変更した PHP ファイルは Pint で整形: `vendor/bin/sail bin pint --dirty --format agent`
- `vendor/bin/sail artisan make:*` でファイル生成（`--no-interaction`）

### テスト

- PHPUnit のみ（Pest は使用しない）
- Feature テストを主、Unit は純粋ロジック向け
- モデルは Factory 経由で作成
- 変更後は関連テストをフィルタ実行: `vendor/bin/sail artisan test --compact --filter=...`

## Development Environment

### Required Tools

- Docker（Sail）
- Composer、Bun（または npm 互換）
- コマンドは **必ず** `vendor/bin/sail` 経由で実行

### Common Commands

```bash
# 起動
vendor/bin/sail up -d

# 開発（サーバー + キュー + Pail + Vite）
vendor/bin/sail composer run dev

# マイグレーション
vendor/bin/sail artisan migrate

# テスト（全体）
vendor/bin/sail artisan test --compact

# フロントビルド
vendor/bin/sail bun run build
vendor/bin/sail bun run dev
```

## Key Technical Decisions

| 決定 | 理由 |
|------|------|
| Sail 必須 | チーム・エージェント間で環境を統一 |
| SQLite（ローカル既定） | セットアップの簡素化（`.env.example`） |
| Boost + Kiro スキル | 仕様駆動と Laravel 公式 AI ガイドラインの併用 |
| Tailwind v4 | Vite プラグイン統合、ユーティリティファースト UI |
| 仕様言語 | 各 spec の `spec.json` の `language` に従う（プロジェクトファイル用） |

## Agent & Spec Tooling

- ルート `AGENTS.md`: Kiro ワークフローと Boost ガイドライン
- `docs/steering/`: プロジェクト全体の永続メモリ（本ディレクトリ）
- `docs/specs/`: 機能単位の要件・設計・タスク
- `.cursor/skills/kiro-*`: 仕様・実装・レビュー用スキル

---
_Document standards and patterns, not every dependency_
