# Design

## Overview

ログインは追加パッケージのないセッション画面である（D1、ADR-NEW-1）。名前付きルート `login` と `dashboard` を置き、保護画面は `auth`、ログイン画面は `guest` に載せる。失敗回数は入力されたメールアドレスごとの直近 60 秒の時刻リストで数える（D5）。一覧の要約と詳細の本文は Blade の `{{ }}` でエスケープした平文にする（D2）。詳細は `/announcements/{announcement}` の整数主キーで、公開対象でなければ HTTP 404 にする（D3）。開発とテストの投入は Factory と Seeder で行い、追記用の Artisan は置かない（D4）。公開判定と一件の取得は `Result` でコントローラへ返し、モデルは `Result` に依存しない。

## Requirements Traceability

| ID | Components | Test Level |
|---|---|---|
| REQ-DSH-NEW-1 | LoginController | Integration |
| REQ-DSH-NEW-2 | welcome | Integration |
| REQ-DSH-NEW-3 | welcome | Integration |
| REQ-DSH-NEW-4 | welcome | Integration |
| REQ-DSH-NEW-5 | LoginController | Integration |
| REQ-DSH-NEW-6 | LoginAttempt | Integration |
| REQ-DSH-NEW-7 | LoginAttempt | Integration |
| REQ-DSH-NEW-8 | LoginController | Integration |
| REQ-DSH-NEW-9 | LoginController | Integration |
| REQ-DSH-NEW-10 | welcome | Integration |
| REQ-DSH-NEW-11 | welcome | Integration |
| REQ-DSH-NEW-12 | routes/web.php | Integration |
| REQ-DSH-NEW-13 | welcome | Integration |
| REQ-DSH-NEW-14 | routes/web.php | Integration |
| NFR-DSH-NEW-1 | LoginAttempt | Security |
| REQ-ANN-NEW-1 | AnnouncementCatalog | Integration |
| REQ-ANN-NEW-2 | AnnouncementCatalog | Integration |
| REQ-ANN-NEW-3 | DashboardController | Integration |
| REQ-ANN-NEW-4 | AnnouncementCatalog | Integration |
| REQ-ANN-NEW-5 | DashboardController | Integration |
| REQ-ANN-NEW-6 | AnnouncementController | Integration |
| REQ-ANN-NEW-7 | AnnouncementController | Integration |
| REQ-ANN-NEW-8 | AnnouncementCatalog | Integration |
| REQ-ANN-NEW-9 | DashboardController | Integration |
| REQ-ANN-NEW-10 | DashboardController | Integration |
| REQ-ANN-NEW-11 | routes/web.php | Integration |
| DATA-ANN-NEW-1 | Announcement | Integration |

## Components and Interfaces

### LoginController
- Location: app/Http/Controllers/LoginController.php（new）
- Responsibility: ログイン画面、セッションの開始と破棄、ウェルカムへの戻りを行う。
- Depends on: LoginAttempt
- Provides: create()、store(email, password)、destroy()

`GET /login` は名前 `login`、`POST /login` は同じ URL、どちらも `guest` に載せる。成功した `store` はセッション ID を再生成し、直前に求めた保護画面か、無ければ `dashboard` を返す。失敗はログインへ戻し、拒否文「メールアドレスまたはパスワードが正しくありません。」だけを出す。`POST /logout` は名前 `logout` で、`auth` には載せない。認証を外し、セッションを破棄して名前 `welcome` の `/` へ戻す。未ログインのログアウトも同じ戻りで、利用者のセッションは始めない。remember me は出さない。

### LoginAttempt
- Location: app/Dashboard/LoginAttempt.php（new）
- Responsibility: 直近 60 秒の失敗が 5 件未満のときだけ `web` ガードで認証し、結果を `Result` で返す。
- Depends on: User、キャッシュ
- Provides: authenticate(email, password)

キャッシュキーは `login-failures:` にメールアドレスの SHA-256 を足したものである。値は失敗時刻のリストで、60 秒より古い時刻は数えない。5 件ある間は `Auth::attempt` を呼ばず失敗を返す。不一致のときだけ現在時刻を足す。上限超過の拒否は不一致の回数に足さない。成功、不一致、上限超過は、コントローラからは同じ拒否文になる。

### DashboardController
- Location: app/Http/Controllers/DashboardController.php（new）
- Responsibility: 公開対象のタイトルと要約を、本文を出さずに一覧する。
- Depends on: AnnouncementCatalog
- Provides: index()

`GET /dashboard` は名前 `dashboard` で、`auth` に載せる。項目は `announcements.show` へのリンクである。公開対象が無いときは「お知らせはありません。」を出し、項目は出さない。作成、編集、削除の操作は出さない。

### AnnouncementController
- Location: app/Http/Controllers/AnnouncementController.php（new）
- Responsibility: 公開対象の一件のタイトルと本文を出す。
- Depends on: AnnouncementCatalog
- Provides: show(announcement)

`GET /announcements/{announcement}` は名前 `announcements.show`、`auth` に載せ、主キーは数値だけを受ける。`findVisible` の失敗は HTTP 404 で、タイトル、要約、本文は出さない。要約の欄は描画しない。作成、編集、削除の操作は出さない。

### AnnouncementCatalog
- Location: app/Announcements/AnnouncementCatalog.php（new）
- Responsibility: 公開対象の一覧と一件の取得を行う。
- Depends on: Announcement、AnnouncementVisibility
- Provides: visible(now)、findVisible(id, now)

一覧は公開開始の新しい順、開始が同じなら作成の新しい順、それも同じならタイトルを `strcmp` の昇順で並べる。`findVisible` は公開対象なら `Result::ok`、それ以外は失敗の `Result` を返す。保存、更新、削除のメソッドは持たない。

### AnnouncementVisibility
- Location: app/Announcements/AnnouncementVisibility.php（new）
- Responsibility: 状態、非空白の三項目、開始以上かつ終了以下を判定する。
- Depends on: AnnouncementStatus
- Provides: isVisible(status, title, summary, body, startsAt, endsAt, now)

空白だけとは、値が無いか、Unicode の空白文字だけであることをいう。時刻はアプリケーションの現在時刻で、開始と終了の瞬間を含む。

### welcome
- Location: resources/views/welcome.blade.php
- Responsibility: 未ログインにはログインへのリンク、ログイン済みにはダッシュボードへのリンクとログアウトを出す。
- Depends on: セッション
- Provides: `/`（名前 `welcome`）

お知らせのタイトル、要約、本文、自己登録、パスワード再設定は出さない。

## Data Model

`announcements` は DATA-ANN-NEW-1 の項目と、詳細の主キー `id` を持つ。`updated_at` は持たない。

| Column | Type | Required | Notes |
|---|---|---|---|
| id | bigint | yes | 詳細 URL の整数 |
| title | text | no | |
| summary | text | no | |
| body | text | no | |
| status | string | yes | `draft` または `published`。PHP の `AnnouncementStatus` |
| starts_at | timestamp | no | UTC |
| ends_at | timestamp | no | UTC |
| created_at | timestamp | yes | UTC |

`AnnouncementStatus` は `App\Enums\AnnouncementStatus` で、case は `Draft` と `Published` である。`AnnouncementFactory` の既定は、三項目に空白以外があり、期間が現在を含む `published` である。`draft()` は状態だけを下書きにする。`AnnouncementSeeder` は公開対象を一件と下書きを一件作り、`DatabaseSeeder` から呼ぶ。

## Error Handling

| Case | Behavior |
|---|---|
| 認証情報が一致しない | ログインへ戻し、同じ拒否文を出す。利用者のセッションは始めない |
| 直近 60 秒の失敗が 5 件ある | パスワードを確かめず、同じ拒否文を出す |
| 未ログインが保護画面を開く | ログインへ移し、お知らせのタイトル、要約、本文は出さない |
| 公開対象でない詳細 | HTTP 404 とし、タイトル、要約、本文は出さない |
| 自己登録、パスワード再設定、お知らせの変更や削除 | 対応するルートは無く、保存済みの値は変わらない |

## Testing Strategy

| Test Level | Runs on | Notes |
|---|---|---|
| Unit | Local | `AnnouncementVisibility` の公開判定と、タイトルの `strcmp` 順 |
| Integration | Local | ログイン、ログアウト、一覧、詳細、空の一覧、404、登録と変更の拒否、項目の保存 |
| Security | CI | 同一メールの直近 60 秒に不一致が 5 件あると、一致するパスワードでも拒否する。別のメールはログインできる |

テストは Pest で書く。既存のスイートもそのまま実行する。

## Migration

画面より先に `announcements` のマイグレーションを適用する。`users`、`sessions`、`password_reset_tokens` は変えない。ローカルの確認データは、マイグレーションのあと `DatabaseSeeder` で利用者とお知らせを入れる。セッションの期限は既存の設定のままにする。

# Delta

## /steering/architecture.md

### SECTIONS

#### Overview
- base: 53986f7aea59

単一の Laravel アプリケーションである。HTTP と Artisan は同じコードベースを起動する。画面は Blade と Vite のアセットで、永続化は Eloquent とマイグレーションで行う。成功と失敗の合成は `App\Support\Result` に閉じ、ログインとお知らせの取得は HTTP が `Result` を読んで応答に変える。

#### Component View
- base: 2d8fb44ad119

##### HTTP

`public/index.php` から `bootstrap/app.php` へ入る。Web ルートは `routes/web.php`、コマンドは `routes/console.php`、ヘルスチェックは `/up` である。ウェルカムはクロージャがビューを返す。ログインとログアウトは `LoginController`、ダッシュボードは `DashboardController`、お知らせ詳細は `AnnouncementController` である。未認証の保護画面は `auth` が名前付きルート `login` へ戻す。`api/*` の例外だけ JSON で描画する。

##### アカウント

`User` は `Authenticatable` で、名前、メール、パスワードを fillable にし、パスワードと remember token を hidden にする。認証の既定ガードは `web`、パスワードブローカは `users` である。ログインは `LoginAttempt` がこのガードのセッションを始める。ファクトリとシーダが確認用の利用者を作る。

##### 結果

`LogEntry` は依存を持たない契約である。`Result` はこの契約と `Illuminate\Support\Collection` に依存する。`map` と `bind` は失敗なら自分を返し、成功なら変換する。`doo` は `Result` だけを yield するジェネレータを順に評価し、ログを連結する。`ResultUnwrapException` は `Result` と同じ支援名前空間の例外である。

##### 起動

`bootstrap/providers.php` は `AppServiceProvider` だけを登録する。`register` と `boot` は空である。

#### Runtime View
- base: 51e4a4f4bbc0

HTTP リクエストは `public/index.php`、HTTP カーネル、Web ミドルウェア、`routes/web.php` または `/up` の順で処理する。保護画面は `auth` を通り、未認証ならログインへ戻る。ログインの成否は `Result` でコントローラへ戻り、失敗なら同じ拒否を出す。Artisan は `routes/console.php` を読む。開発コマンド `composer dev` は `php artisan serve`、`queue:listen`、`pail`、`npm run dev` を同時に起動する。`Result` の合成は呼び出し元のプロセス内で終わり、キューやイベントとしては配送しない。

## /steering/structure.md

### SECTIONS

#### Module Boundaries
- base: f92d4c1a0d40

`app/` の現在の境界は次のとおりである。

| 名前空間 | 責務 |
| --- | --- |
| `App\Contracts` | メソッドを持たない `LogEntry` |
| `App\Support` | `Result` と `ResultUnwrapException` |
| `App\Enums` | `AnnouncementStatus` |
| `App\Models` | `User` と `Announcement` |
| `App\Announcements` | 公開判定と `AnnouncementCatalog` |
| `App\Dashboard` | `LoginAttempt` |
| `App\Http\Controllers` | ログイン、ダッシュボード、お知らせ詳細 |
| `App\Providers` | 空の `AppServiceProvider` |

ウェルカムはクロージャが `welcome` ビューを返す。ログイン、ダッシュボード、お知らせ詳細はコントローラが返す。`resources/js/app.js` は空である。スタイルのエントリは `resources/css/app.css` である。

#### Dependency Rules
- base: e758cb81461c

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

#### Code-to-Specs Mapping
- base: bfe3f12031d7

振る舞いの仕様は `docs/sdd/specs/<domain>/<capability>/` に置く。ドメインの一覧は [仕様](/specs/) である。

| コード | 仕様上の置き場 |
| --- | --- |
| `routes/web.php`、`resources/views/welcome.blade.php`、`app/Http/Controllers/LoginController.php`、`app/Dashboard/LoginAttempt.php`、`resources/views/auth/login.blade.php` | [ダッシュボードへのアクセス](/specs/dashboard/access/) |
| `app/Http/Controllers/DashboardController.php`、`resources/views/dashboard/index.blade.php` | [ダッシュボードへのアクセス](/specs/dashboard/access/) と [お知らせの閲覧](/specs/announcements/read/) |
| `app/Announcements/`、`app/Http/Controllers/AnnouncementController.php`、`app/Models/Announcement.php`、`app/Enums/AnnouncementStatus.php`、`resources/views/announcements/show.blade.php`、お知らせのマイグレーション | [お知らせの閲覧](/specs/announcements/read/) |
| `/up` | ヘルスチェック。能力仕様は無い |
| `app/Models/User.php`、利用者・セッション・パスワード再設定のマイグレーション | アカウントの能力仕様 |
| `app/Support/Result.php`、`app/Support/Exceptions/ResultUnwrapException.php`、`app/Contracts/LogEntry.php` | 結果の能力仕様 |

`docs/specs/` にある Kiro 形式の文書は、上記の SDD 仕様へ自動では写像しない。決定は [決定](/decision/) に置く。
