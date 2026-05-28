# Project Structure

## Organization Philosophy

**Laravel 標準レイヤー + 仕様駆動の横断ドキュメント**。アプリケーションコードはフレームワーク慣習（Models / Http / Providers）に従い、機能追加は `docs/specs/{feature}/` で仕様化してから `app/` に実装する。プロジェクト全体の原則は `docs/steering/`、機能固有の契約は spec 配下または該当ディレクトリの `AGENTS.md` に置く。

## Directory Patterns

### Application Layer (`app/`)

**Purpose**: ドメインロジック、HTTP、サービスプロバイダ  
**Convention**:

- `app/Models/` — Eloquent モデル、Factory と対応
- `app/Http/Controllers/` — 薄いコントローラ、バリデーションは Form Request へ
- `app/Providers/` — バインディング・ブートストラップ

新規クラスは `vendor/bin/sail artisan make:class` 等の Artisan 生成を優先。

### Routes (`routes/`)

**Purpose**: HTTP / コンソールエントリポイント  
**Example**: `web.php` で名前付きルート、`route()` ヘルパで URL 生成

### Resources (`resources/`)

**Purpose**: フロントエンドとビュー  
**Pattern**:

- `resources/views/` — Blade テンプレート
- `resources/css/app.css`, `resources/js/app.js` — Vite エントリ（`vite.config.js` で宣言）

### Database (`database/`)

**Purpose**: スキーマとテストデータ  
**Pattern**: `migrations/`、`factories/`、`seeders/` — テストでは Factory を優先

### Tests (`tests/`)

**Purpose**: 自動検証  
**Pattern**:

- `tests/Feature/` — HTTP・統合フロー（主戦場）
- `tests/Unit/` — 隔離された単体ロジック
- ベース: `tests/TestCase.php`

### Project Memory (`docs/`)

**Purpose**: エージェントと人間の共有コンテキスト（アプリコード外）  

| パス | 役割 |
|------|------|
| `docs/steering/` | プロジェクト全体の原則・スタック・構造（本ファイル群） |
| `docs/specs/{feature}/` | 機能別 requirements / design / tasks |
| `docs/settings/` | テンプレート等メタデータ（steering 本文には載せない） |

### Specifications Workflow

機能開発の標準フロー（詳細はルート `AGENTS.md`）:

1. `/kiro-discovery` または `/kiro-spec-init` で spec 開始
2. requirements → design → tasks（各フェーズ人間レビュー）
3. `/kiro-impl {feature}` で TDD 実装

## Naming Conventions

- **PHP クラス**: PascalCase（`UserController`）
- **ファイル**: クラス名と一致（`User.php`）
- **メソッド**: camelCase、述語で意図を表す（`isRegisteredForDiscounts`）
- **Enum キー**: TitleCase（`Monthly`）
- **ルート名**: dot.notation（Laravel 慣習）
- **Blade**: kebab-case またはドット区切りビュー名

## Import Organization

```php
// フレームワーク
use Illuminate\Support\Facades\Route;

// アプリケーション（App\ プレフィックス）
use App\Models\User;

// 同一名前空間は相対不要 — 完全修飾または use で明示
```

**PSR-4 ルート**:

| プレフィックス | ディレクトリ |
|----------------|-------------|
| `App\` | `app/` |
| `Database\Factories\` | `database/factories/` |
| `Database\Seeders\` | `database/seeders/` |
| `Tests\` | `tests/` |

## Code Organization Principles

1. **Thin controllers** — ビジネスロジックはサービスクラスまたはモデルメソッドへ
2. **Authorization** — Policy / Gate を API・Web 共通で使用
3. **Validation** — Form Request クラスに集約
4. **API** — 既存規約に合わせ Eloquent API Resource とバージョニング
5. **新規ディレクトリ** — ルート直下の新ベースフォルダは承認なしに作らない
6. **エージェント専用ツール** — `.cursor/` 等の詳細は steering に列挙しない（パターンのみ）

## Local Feature Context

サブシステムやライブラリ単位で追加の `AGENTS.md` を置ける（例: `app/Services/Billing/AGENTS.md`）。steering は横断原則、ローカル `AGENTS.md` はその境界内の契約とテスト慣習を記述する。

---
_Document patterns, not file trees. New files following patterns shouldn't require updates_
