---
type: "Steering"
title: "構造"
description: "リポジトリの配置、モジュール境界、命名、依存の向き、仕様との対応。"
tags: [structure]
---
# 構造

## Repository Layout

| パス | 役割 |
| --- | --- |
| `app/` | アプリケーションコード。PSR-4 の `App\` |
| `bootstrap/` | アプリケーション組み立てとプロバイダ登録 |
| `config/` | 設定 |
| `database/` | マイグレーション、ファクトリ、シーダ |
| `public/` | フロントコントローラ |
| `resources/` | Blade、CSS、JS |
| `routes/` | HTTP とコンソールの入口 |
| `tests/` | Pest と PHPUnit。PSR-4 の `Tests\` |
| `docs/sdd/` | 仕様、決定、このステアリング |
| `docs/specs/` | Kiro 形式の仕様木。`docs/sdd/specs/` とは別である |
| `docs/steering/` | Kiro 形式のプロジェクトメモリ。このディレクトリとは別である |
| `docs/settings/` | 文書テンプレート |
| `.agents/skills/` | SDD スキル |
| `compose.yaml` | Sail の `laravel.test` |

Composer の PSR-4 は `App\` が `app/`、`Database\Factories\` が `database/factories/`、`Database\Seeders\` が `database/seeders/`、`Tests\` が `tests/` である。

## Module Boundaries
`app/` の現在の境界は次のとおりである。

| 名前空間 | 責務 |
| --- | --- |
| `App\Contracts` | メソッドを持たない `LogEntry` |
| `App\Support` | `Result` と `ResultUnwrapException` |
| `App\Enums` | `AnnouncementStatus` |
| `App\Models` | `User` と `Announcement` |
| `App\Announcements` | 公開判定、`AnnouncementCatalog`、下書きの登録 |
| `App\Dashboard` | `LoginAttempt` |
| `App\Http\Controllers` | ログイン、ダッシュボード、お知らせ詳細、お知らせの登録 |
| `App\Providers` | 空の `AppServiceProvider` |

ウェルカムはクロージャが `welcome` ビューを返す。ログイン、ダッシュボード、お知らせ詳細、お知らせの登録はコントローラが返す。`resources/js/app.js` は空である。スタイルのエントリは `resources/css/app.css` である。

## Naming Conventions
- クラスとファイル名は PascalCase で一致させる。
- メソッドと関数は camelCase にする。真偽を返す名前は `isOk` のように述語にする。
- PHPUnit のテストメソッドは `test_` と snake_case である。Pest の説明文は日本語である。
- ルート名を付けるときはドット区切りにし、URL 生成は `route()` を使う。
- ビュー名はドット区切りである。現在の画面は `welcome`、`auth.login`、`dashboard.index`、`announcements.show`、`announcements.register`、`announcements.registered` である。
- 設定キーは Laravel のファイル名とドット記法に従う。

## Dependency Rules
依存は内側の契約から外側の配送へ向かわせる。

- `App\Contracts` は `App\Support`、`App\Http`、`App\Models` に依存しない。
- `App\Enums` は他の `App\` に依存しない。
- `App\Support` は `App\Contracts` とフレームワークのコレクションに依存してよい。HTTP、Eloquent、設定には依存しない。
- `App\Models` は HTTP と `Result` に依存しない。`App\Enums` には依存してよい。
- `App\Announcements` は `App\Models`、`App\Support`、`App\Enums` に依存してよい。HTTP と `App\Dashboard` には依存しない。
- `App\Dashboard` は `App\Models`、`App\Support`、フレームワークの認証とキャッシュに依存してよい。HTTP と `App\Announcements` には依存しない。
- `App\Http` はモデル、支援型、契約、`App\Announcements`、`App\Dashboard` を呼んでよい。
- ルート定義はビューとフレームワークのファサードに留める。ドメインの合成をクロージャへ長く書かない。
- テストは `App\` を参照する。アプリケーションコードは `Tests\` を参照しない。

## Code-to-Specs Mapping
振る舞いの仕様は `docs/sdd/specs/<domain>/<capability>/` に置く。ドメインの一覧は [仕様](/specs/) である。

| コード | 仕様上の置き場 |
| --- | --- |
| `routes/web.php`、`resources/views/welcome.blade.php`、`app/Http/Controllers/LoginController.php`、`app/Dashboard/LoginAttempt.php`、`resources/views/auth/login.blade.php` | [ダッシュボードへのアクセス](/specs/dashboard/access/) |
| `app/Http/Controllers/DashboardController.php`、`resources/views/dashboard/index.blade.php` | [ダッシュボードへのアクセス](/specs/dashboard/access/) と [お知らせの閲覧](/specs/announcements/read/) と [お知らせの登録](/specs/announcements/register/) |
| `app/Announcements/AnnouncementVisibility.php`、`app/Announcements/AnnouncementCatalog.php`、`app/Http/Controllers/AnnouncementController.php`、`app/Models/Announcement.php`、`app/Enums/AnnouncementStatus.php`、`resources/views/announcements/show.blade.php`、お知らせのマイグレーション | [お知らせの閲覧](/specs/announcements/read/) |
| `app/Announcements/AnnouncementRegistrar.php`、`app/Http/Controllers/AnnouncementRegistrationController.php`、`resources/views/announcements/register.blade.php`、`resources/views/announcements/registered.blade.php`、`app/Models/User.php` の `admin` と、その列を追加するマイグレーション | [お知らせの登録](/specs/announcements/register/) |
| `/up` | ヘルスチェック。能力仕様は無い |
| `app/Models/User.php`、利用者・セッション・パスワード再設定のマイグレーション | アカウントの能力仕様 |
| `app/Support/Result.php`、`app/Support/Exceptions/ResultUnwrapException.php`、`app/Contracts/LogEntry.php` | 結果の能力仕様 |

`docs/specs/` にある Kiro 形式の文書は、上記の SDD 仕様へ自動では写像しない。決定は [決定](/decision/) に置く。

## Related

- [プロダクト](/steering/product.md)
- [用語集](/steering/glossary.md)
- [アーキテクチャ](/steering/architecture.md)
- [技術](/steering/tech.md)
- [仕様](/specs/)
- [決定](/decision/)
