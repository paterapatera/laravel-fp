---
type: "Steering"
title: "プロダクト"
description: "このアプリケーションの目的、利用者、現在の能力、外部システム、運用シナリオ、制約。"
tags: [product]
---
# プロダクト

## Purpose
このリポジトリは Laravel アプリケーションである。未ログインの訪問者が最初に見る画面はウェルカムページである。用意された利用者はログインし、認証済みの画面を使える。アプリケーション固有のドメインコードは、処理の成功と失敗を値として運ぶ `Result` と、その値に付随する `LogEntry` である。利用者アカウントのテーブルと Eloquent モデルは用意してある。

振る舞いの詳細は [仕様](/specs/) のドメインに置く。品質の数値目標も仕様に置く。

## Stakeholders and Users
- 開発者は Sail 上でアプリケーションを変更し、Pest と Larastan と Pint で確認する。
- ブラウザの訪問者は `/` のウェルカムページを見る。未ログインの訪問者はそこからログインへ進める。
- 用意された利用者はログインし、ダッシュボードでお知らせを読む。
- 監視は `/up` でプロセスの生存を確認する。
- ローカルのシードは `test@example.com` の利用者を 1 件作る。

## Capabilities Overview

仕様ドメインの一覧は [仕様](/specs/) である。現在のコードは次のまとまりを持つ。

### Web

`routes/web.php` は `/` で `welcome` ビューを返す。`bootstrap/app.php` は `/up` をヘルスチェックとして登録する。コンソールは `routes/console.php` の `inspire` を提供する。

### アカウント

`App\Models\User` はセッションガードの Eloquent 利用者である。マイグレーションは `users`、`password_reset_tokens`、`sessions` を作る。パスワードは `hashed` キャストで保存する。

### 結果

`App\Support\Result` は成功値か失敗理由のどちらか一方と、`LogEntry` のコレクションを持つ。呼び出し元は `ok`、`err`、`map`、`bind`、`doo` で合成する。失敗した値から `get` すると `ResultUnwrapException` になる。`LogEntry` はメソッドを持たないマーカーであり、出力形式と永続化は契約の外である。

## External Systems

実行時に必須の外部サービスは無い。ローカルの例示環境は SQLite ファイル、ログメーラ、ログブロードキャストを使う。

開発時の Vite は Instrument Sans を Bunny Fonts から読み込む。本番用のメールサーバ、Redis、オブジェクトストレージの接続先はリポジトリの実行定義に含めていない。

## Operational Scenarios
- 開発者は Sail を起動し、ブラウザで `/` を開く。ウェルカムページは HTTP 200 を返す。
- 監視は `/up` を呼ぶ。
- 開発者はマイグレーションのあと、データベースドライバのセッション、キャッシュ、キューを使う。`composer dev` は HTTP サーバ、`queue:listen`、Pail、Vite を同時に動かす。
- アプリケーションコードは `Result` を関数の戻り値として渡し、付随する `LogEntry` を呼び出し元が解釈する。このログは `LOG_CHANNEL` のアプリケーションログとは別のメモリ上のコレクションである。
- 開発者は `DatabaseSeeder` で確認用の利用者を 1 件作る。
- 用意された利用者はウェルカムのログインから入り、ダッシュボードでお知らせを読む。
- 未ログインの訪問者がダッシュボードまたはお知らせの詳細を開くと、ログインへ移り、お知らせは見えない。
- 利用者はログアウトすると、未認証のウェルカムに戻る。

## Constraints

- 実行する PHP は 8.3 以上である。Sail のイメージは PHP 8.5 である。
- ローカル HTTP の公開ポートは `APP_PORT` で、未設定時は 80 である。Vite のポートは `VITE_PORT` で、未設定時は 5173 である。
- 例示環境の既定データベースは SQLite である。セッション、キャッシュ、キューのドライバは database である。
- HTTP のルート定義は `routes/web.php` である。`api/*` で起きた例外は JSON で描画する。
- `Result` のコンストラクタは private である。インスタンスは `Result::ok` と `Result::err` から作る。
- `APP_KEY` は環境ごとに生成する。秘密情報はリポジトリにコミットしない。

## Related

- [用語集](/steering/glossary.md)
- [アーキテクチャ](/steering/architecture.md)
- [技術](/steering/tech.md)
- [構造](/steering/structure.md)
- [仕様](/specs/)
- [決定](/decision/)
