---
type: "Steering"
title: "アーキテクチャ"
description: "現在の実行構造、モジュール、配置、横断的な関心事。"
tags: [architecture]
---
# アーキテクチャ

## Overview
単一の Laravel アプリケーションである。HTTP と Artisan は同じコードベースを起動する。画面は Blade と Vite のアセットで、永続化は Eloquent とマイグレーションで行う。成功と失敗の合成は `App\Support\Result` に閉じ、ログインとお知らせの取得は HTTP が `Result` を読んで応答に変える。

## Quality Priorities

優先する性質は次のとおりである。数値の目標は [仕様](/specs/) に置く。

- 成功値と失敗理由を同時に持たない。失敗した `Result` から成功値を取り出した場合は例外になる。
- `app/` は Larastan レベル 8 で検査できる形を保つ。`Result` は `@phpstan-immutable` である。
- 変更は Laravel のディレクトリと Artisan の生成物の形に合わせる。

## Context View

ブラウザは Sail の `laravel.test` に HTTP でアクセスする。データはプロセス隣の SQLite ファイルに書く。開発時の Vite は Blade の更新を監視し、Instrument Sans を Bunny Fonts から読む。メールとブロードキャストの例示ドライバはログである。別プロセスのデータベース、Redis、キューワーカー用ブローカは compose に定義していない。

## Component View
### HTTP

`public/index.php` から `bootstrap/app.php` へ入る。Web ルートは `routes/web.php`、コマンドは `routes/console.php`、ヘルスチェックは `/up` である。ウェルカムはクロージャがビューを返す。ログインとログアウトは `LoginController`、ダッシュボードは `DashboardController`、お知らせ詳細は `AnnouncementController`、お知らせの登録は `AnnouncementRegistrationController` である。未認証の保護画面は `auth` が名前付きルート `login` へ戻す。`api/*` の例外だけ JSON で描画する。

### アカウント

`User` は `Authenticatable` で、名前、メール、パスワードを fillable にし、`admin` は fillable にしない。`admin` は真偽で、既定は偽であり、真の利用者が管理者である。パスワードと remember token を hidden にする。認証の既定ガードは `web`、パスワードブローカは `users` である。ログインは `LoginAttempt` がこのガードのセッションを始める。ファクトリとシーダが確認用の利用者を作る。

### 結果

`LogEntry` は依存を持たない契約である。`Result` はこの契約と `Illuminate\Support\Collection` に依存する。`map` と `bind` は失敗なら自分を返し、成功なら変換する。`doo` は `Result` だけを yield するジェネレータを順に評価し、ログを連結する。`ResultUnwrapException` は `Result` と同じ支援名前空間の例外である。

### 起動

`bootstrap/providers.php` は `AppServiceProvider` だけを登録する。`register` と `boot` は空である。

Rationale: [ADR-2](/decision/2-user-admin-flag.md)

## Runtime View
HTTP リクエストは `public/index.php`、HTTP カーネル、Web ミドルウェア、`routes/web.php` または `/up` の順で処理する。保護画面は `auth` を通り、未認証ならログインへ戻る。ログインの成否は `Result` でコントローラへ戻り、失敗なら同じ拒否を出す。Artisan は `routes/console.php` を読む。開発コマンド `composer dev` は `php artisan serve`、`queue:listen`、`pail`、`npm run dev` を同時に起動する。`Result` の合成は呼び出し元のプロセス内で終わり、キューやイベントとしては配送しない。

## Deployment View

リポジトリが定義する実行単位は次の二つである。

- アプリケーションの入口は `public/index.php` である。
- ローカル開発は `compose.yaml` の `laravel.test` である。イメージは `sail-8.5/app` で、公開ポートは `APP_PORT`（既定 80）と `VITE_PORT`（既定 5173）である。`depends_on` は空である。
- Dev Container は同じ compose ファイルの `laravel.test` を `remoteUser: sail` で開く。

セッション、キャッシュ、キューの例示ドライバは database なので、それらの経路を使う前にマイグレーションで `sessions`、`cache`、`jobs` を作る。ファイルの既定ディスクは `local` である。

## Cross-cutting Concerns

- 設定は `config/` と環境変数である。例示の雛形は `.env.example` である。
- ログチャネルの既定は `stack` と `single` である。`Result` が持つ `LogEntry` はこのチャネルへ自動では出さない。
- テスト時は `phpunit.xml` がキャッシュを array、キューを sync、セッションを array、メールを array に切り替える。データベース名は `testing` である。
- パスワードはモデルのキャストでハッシュする。
- 新しいルート直下のディレクトリは、アプリケーションの境界を変えるので、ステアリングの更新と合わせて追加する。

## Related

- [プロダクト](/steering/product.md)
- [用語集](/steering/glossary.md)
- [技術](/steering/tech.md)
- [構造](/steering/structure.md)
- [決定](/decision/)
