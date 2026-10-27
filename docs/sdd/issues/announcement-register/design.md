# Design

## Overview

管理者は利用者の `admin` が真であることである (D1, ADR-NEW-1)。フラグは fillable に入れず、登録の入力では変えない。管理者がダッシュボードから `/announcements/register` を開き、`AnnouncementRegistrar` が空白を拒んで draft を一件追加する。開始と終了は常に空である。保存に成功したら GET の成功画面へ移し、題名だけを文字として出す (D2)。管理者でないログイン済みの利用者には 404 を返し、未ログインはログインへ戻す。

## Requirements Traceability

| ID | Components | Test Level |
|---|---|---|
| REQ-ANN-10 | dashboard/index.blade.php、announcements/show.blade.php | Integration |
| REQ-ANR-NEW-1 | dashboard/index.blade.php | Integration |
| REQ-ANR-NEW-2 | dashboard/index.blade.php | Integration |
| REQ-ANR-NEW-3 | welcome.blade.php、announcements/show.blade.php、announcements/registered.blade.php | Integration |
| REQ-ANR-NEW-4 | announcements/register.blade.php | Integration |
| REQ-ANR-NEW-5 | auth ミドルウェア | Integration |
| REQ-ANR-NEW-6 | auth ミドルウェア | Integration |
| REQ-ANR-NEW-7 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-8 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-9 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-10 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-11 | AnnouncementRegistrar | Unit |
| REQ-ANR-NEW-12 | announcements/register.blade.php | Integration |
| REQ-ANR-NEW-13 | announcements/register.blade.php | Integration |
| REQ-ANR-NEW-14 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-15 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-16 | announcements/registered.blade.php | Integration |
| REQ-ANR-NEW-17 | announcements/registered.blade.php | Integration |
| REQ-ANR-NEW-18 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-19 | User | Integration |
| DATA-ANR-NEW-1 | User | Integration |

## Components and Interfaces

### AnnouncementRegistrar
- Location: app/Announcements/AnnouncementRegistrar.php (new)
- Responsibility: 題名、要約、本文のうち、Unicode の空白だけを除くと空になるものを拒否する。それ以外は入力の文字のまま、状態 draft、開始と終了が空の下書きを一件追加する。
- Depends on: Announcement、AnnouncementStatus、Result
- Provides: register(title, summary, body)

### AnnouncementRegistrationController
- Location: app/Http/Controllers/AnnouncementRegistrationController.php (new)
- Responsibility: `auth` の内側で、GET と POST の `/announcements/register`、成功後の GET `/announcements/registered` を扱う。ルート名は `announcements.register.create`、`announcements.register.store`、`announcements.register.created` とする。`announcements.store` は作らない。`admin` が真でなければ 404 を返す。保存に成功したら題名だけをセッションに載せて GET へ移す。
- Depends on: AnnouncementRegistrar、利用者の `admin`
- Provides: create、store、created

### Registration views
- Location: resources/views/announcements/register.blade.php、resources/views/announcements/registered.blade.php (new)
- Responsibility: 登録画面は空の題名、要約、本文を出し、不足がある項目だけに文を出す。成功画面は題名の文字だけを出す。どちらも `{{ }}` でエスケープし、登録へのリンクは出さない。
- Depends on: コントローラが渡す入力、不足、題名
- Provides: 登録画面と成功画面

### Dashboard registration link
- Location: resources/views/dashboard/index.blade.php
- Responsibility: `admin` が真のときだけ、文言「登録」で登録画面へのリンクを出す。編集と削除の操作は出さない。ウェルカム、詳細、成功画面にはこのリンクを置かない。
- Depends on: 利用者の `admin`
- Provides: 登録画面へのリンク

### User
- Location: app/Models/User.php と users への追加マイグレーション
- Responsibility: `admin` を真偽で保持し、fillable には入れない。未指定の利用者は偽である。
- Depends on: なし
- Provides: admin

## Data Model

`users.admin` は必須の真偽で、既定は偽である。お知らせの項目は DATA-ANN-1 のままである。登録で増える下書きは、送られた題名、要約、本文、状態 draft、空の開始と終了、作成時刻を持つ。

## Error Handling

| Case | Behavior |
|---|---|
| 未ログインが登録 URL を開く、または登録を求める | ログインへ移し、お知らせは増えない |
| 管理者でないログイン済み利用者が登録 URL を開く、または登録を求める | 404 を返し、登録入力も題名も本文も出さない。お知らせは増えない |
| 題名、要約、本文のいずれかが空白だけ | 保存しない。空白の項目だけに「題名がありません」「要約がありません」「本文がありません」を出し、入力した文字は残す |
| 開始、終了、状態が送られる | 開始と終了は空、状態は draft で保存する |
| 管理者フラグを付ける、または外す入力が送られる | その利用者の `admin` は変わらない |
| 成功画面を再表示する | 下書きは増えない |

## Testing Strategy

| Test Level | Runs on | Notes |
|---|---|---|
| Unit | Local | 空白だけの拒否と、空白以外の文字を残すこと |
| Integration | Local | リンク、ログインへの移動、404、draft の追加、成功画面、フラグが変わらないこと。既存のお知らせとダッシュボードのテストも通す |

管理者はテストがファクトリで `admin` を真にして作る。シーダと付与の画面は足さない。

## Migration

登録画面を使う前に、`users` へ `admin` を既定偽で追加する。お知らせのテーブルは変えない。

# Delta

## /steering/architecture.md

### SECTIONS

#### Component View
- base: 8e201a479cec
- rationale: ADR-NEW-1

##### HTTP

`public/index.php` から `bootstrap/app.php` へ入る。Web ルートは `routes/web.php`、コマンドは `routes/console.php`、ヘルスチェックは `/up` である。ウェルカムはクロージャがビューを返す。ログインとログアウトは `LoginController`、ダッシュボードは `DashboardController`、お知らせ詳細は `AnnouncementController`、お知らせの登録は `AnnouncementRegistrationController` である。未認証の保護画面は `auth` が名前付きルート `login` へ戻す。`api/*` の例外だけ JSON で描画する。

##### アカウント

`User` は `Authenticatable` で、名前、メール、パスワードを fillable にし、`admin` は fillable にしない。`admin` は真偽で、既定は偽であり、真の利用者が管理者である。パスワードと remember token を hidden にする。認証の既定ガードは `web`、パスワードブローカは `users` である。ログインは `LoginAttempt` がこのガードのセッションを始める。ファクトリとシーダが確認用の利用者を作る。

##### 結果

`LogEntry` は依存を持たない契約である。`Result` はこの契約と `Illuminate\Support\Collection` に依存する。`map` と `bind` は失敗なら自分を返し、成功なら変換する。`doo` は `Result` だけを yield するジェネレータを順に評価し、ログを連結する。`ResultUnwrapException` は `Result` と同じ支援名前空間の例外である。

##### 起動

`bootstrap/providers.php` は `AppServiceProvider` だけを登録する。`register` と `boot` は空である。

## /steering/structure.md

### SECTIONS

#### Module Boundaries
- base: 45a775be3d51

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

#### Naming Conventions
- base: 34d455da84ae

- クラスとファイル名は PascalCase で一致させる。
- メソッドと関数は camelCase にする。真偽を返す名前は `isOk` のように述語にする。
- PHPUnit のテストメソッドは `test_` と snake_case である。Pest の説明文は日本語である。
- ルート名を付けるときはドット区切りにし、URL 生成は `route()` を使う。
- ビュー名はドット区切りである。現在の画面は `welcome`、`auth.login`、`dashboard.index`、`announcements.show`、`announcements.register`、`announcements.registered` である。
- 設定キーは Laravel のファイル名とドット記法に従う。

#### Code-to-Specs Mapping
- base: c15b35cc3931

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
