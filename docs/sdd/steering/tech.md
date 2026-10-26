---
type: "Steering"
title: "技術"
description: "採用しているスタック、開発環境、常用コマンド、コーディングとテストのやり方。"
tags: [tech]
---

# 技術

## Stack

| 領域 | 採用 |
| --- | --- |
| 言語 | PHP `^8.3`。Sail ランタイムは 8.5 |
| フレームワーク | `laravel/framework` `^13.8` |
| HTTP ビュー | Blade。ウェルカムは `resources/views/welcome.blade.php` |
| フロントエンド | Vite 8、`laravel-vite-plugin`、Tailwind CSS 4 |
| パッケージ | Composer と Bun。JavaScript のロックは `bun.lock`。`composer setup` は npm を呼ぶ |
| テスト | Pest 4、`pestphp/pest-plugin-laravel`、PHPUnit 12 |
| 静的解析 | Larastan 3。`phpstan.neon.dist` のレベル 8 |
| 整形 | Laravel Pint |
| 開発補助 | Laravel Boost、Pail、Tinker、Sail、Collision、Faker、Mockery |

アプリケーション依存はフレームワークと Tinker である。それ以外は require-dev に置く。

## Development Environment

ローカルのサーバ、PHP、Artisan、Composer、Node は Sail 経由で実行する。定義は `compose.yaml` の `laravel.test` だけである。Dev Container もこのサービスを開く。

`.env.example` の主な選択は次のとおりである。

- `DB_CONNECTION=sqlite`
- `SESSION_DRIVER=database`
- `QUEUE_CONNECTION=database`
- `CACHE_STORE=database`
- `MAIL_MAILER=log`
- `BROADCAST_CONNECTION=log`
- `FILESYSTEM_DISK=local`

`composer setup` は依存の導入、`.env` の複製、`key:generate`、`migrate --force`、npm install、フロントの build を行う。npm は `.npmrc` で `ignore-scripts=true` である。

## Common Commands

```bash
vendor/bin/sail up -d
vendor/bin/sail composer setup
vendor/bin/sail composer run dev
vendor/bin/sail artisan migrate
vendor/bin/sail artisan test --compact
vendor/bin/sail bin pint --dirty --format agent
vendor/bin/sail php vendor/bin/phpstan analyse
vendor/bin/sail bun run dev
vendor/bin/sail bun run build
```

`composer dev` はサーバ、キューリスナ、Pail、Vite を concurrently で起動する。テスト用スクリプト `composer test` は設定キャッシュを消してから `artisan test` を実行する。

## Coding Conventions

- 制御構造には波括弧を付ける。
- 引数と戻り値に型を書く。コンストラクタはプロパティプロモーションを使う。
- Eloquent の fillable と hidden は PHP 属性で書く。キャストは `casts()` で返す。
- 新しいクラス、モデル、テストは Artisan の `make:*` で作り、`--no-interaction` を付ける。
- 変更した PHP は Pint で整形する。
- `Result` のような値オブジェクトは `final` と private コンストラクタにし、PHPDoc で成功型と失敗型を残す。
- 列挙型の case 名は TitleCase にする。

## Testing Approach

テストランナーは Pest で、設定ファイルは `phpunit.xml` である。スイートは `tests/Unit` と `tests/Feature` である。

- `tests/Pest.php` は Feature に `Tests\TestCase` を使い、Unit に `PHPUnit\Framework\TestCase` を使う。
- `Result` と `LogEntry` のテストは Pest の `test()` で書き、説明は日本語である。
- 枠組みのサンプルである `tests/Feature/ExampleTest.php` と `tests/Unit/ExampleTest.php` は PHPUnit クラスである。Feature のサンプルは `/` が 200 であることを確認する。
- モデルをテストで作るときはファクトリを使う。
- 変更に対応するテストを `--filter` で実行する。静的解析は `app/` を対象にする。

## Related

- [プロダクト](/steering/product.md)
- [用語集](/steering/glossary.md)
- [アーキテクチャ](/steering/architecture.md)
- [構造](/steering/structure.md)
