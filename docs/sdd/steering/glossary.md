---
type: "Steering"
title: "用語集"
description: "このリポジトリで意味を固定する用語。"
tags: [glossary]
---

# 用語集

## Terms

### Bunny Fonts

Vite が Instrument Sans を取得するフォントの提供元である。設定は `vite.config.js` の `bunny('Instrument Sans')` にある。

### Dev Container

`.devcontainer/devcontainer.json` が定義する開発用コンテナである。`compose.yaml` の `laravel.test` をワークスペースにする。

### Eloquent

Laravel の ORM である。このアプリケーションでは `App\Models\User` が利用者を表す。

### Larastan

`app/` に対する PHPStan の Laravel 拡張である。設定は `phpstan.neon.dist` で、レベルは 8 である。

### Laravel Sail

このリポジトリのローカル実行環境である。定義は `compose.yaml` にあり、PHP 8.5 の `laravel.test` を 1 台起動する。

### LogEntry

`App\Contracts\LogEntry` である。メソッドを持たないマーカーインターフェースで、`Result` に添付できる診断エントリを表す。出力と永続化の形式は実装側が決める。

### Pest

テストの記述に使っているランナーである。`tests/Pest.php` が Feature と Unit の基底を分け、PHPUnit の `phpunit.xml` を設定として使う。

### Pint

PHP のコードフォーマッタである。変更した PHP は Pint で整形する。

### Result

`App\Support\Result` である。成功値と失敗理由の一方と、`LogEntry` のコレクションを不変の値として持つ。成功値の取得に失敗すると `ResultUnwrapException` を投げる。

### ResultUnwrapException

失敗状態の `Result` に対して `get` を呼んだときに投げる `LogicException` である。失敗理由は `error` で読む。

### SQLite

例示環境と設定の既定データベースである。接続ファイルの既定パスは `database/database.sqlite` である。Sail の compose にデータベース用コンテナは無い。

### Tailwind CSS

画面のユーティリティ CSS である。バージョン 4 を `@tailwindcss/vite` で Vite に組み込む。エントリは `resources/css/app.css` である。

### Vite

フロントエンドの開発サーバとビルドである。エントリは `resources/css/app.css` と `resources/js/app.js` である。

## Related

- [プロダクト](/steering/product.md)
- [アーキテクチャ](/steering/architecture.md)
- [技術](/steering/tech.md)
- [構造](/steering/structure.md)
