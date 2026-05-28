# Brief: functional-result

## Problem
PHP 8.5 のパイプ演算子 `|>` や、PHP 8.6 以降で想定される部分適用などにより、PHP でも関数型プログラミングに寄せた記述を増やせる見込みがある。

アプリケーションのサービス層で例外やログを個別に扱うと、成功値・失敗値・処理ログの受け渡しが散らばりやすく、チェーン処理や早期中断の型が読み取りにくくなる。

## Current State
現状のプロジェクトは Laravel スケルトン段階で、`Result`、`Either`、`Writer`、`LogEntry` に相当する共通ユーティリティはまだ存在しない。

ステアリングでは Laravel 標準構成、明示的な型、PHPUnit、Pint、Sail 経由の実行が前提になっている。純粋な共通ユーティリティは `app/Support/`、契約は `app/Contracts/`、単体テストは `tests/Unit/` に置くのが自然である。

## Desired Outcome
Either モナドと Writer モナドを合わせたような `Result` 型を用意し、成功値または失敗値と、処理中に発生したログエントリの collection を一緒に扱えるようにする。

`Result::ok($value, collect([...]))`、`Result::err($throwable, collect([...]))`、`map`、`bind`、`get`、`getOr`、`isOk`、`isErr` を提供し、パイプ演算子でも読みやすく合成できる API を目指す。

`Result::doo()` は Generator ベースで複数の `Result` を逐次評価し、すべて成功した場合は最終 `Result::ok` を返す。途中で `err` が返った場合は処理を中断し、その時点までのログ collection を結合した `Result::err` を返す。

## Approach
単一の immutable `Result` クラスとして実装する。

`Result` は `app/Support/Result.php` に配置し、状態は ok / err のどちらか一方だけを保持する。ログは `Illuminate\Support\Collection<int, App\Contracts\LogEntry>` として保持し、`map`、`bind`、`doo` の各段階で結合する。

PHPStan で可能な限り型を追えるよう、クラスレベルの `@template` は不変として扱う。`@template-covariant` は `map` / `bind` の callable 引数位置と衝突しやすいため使わない。`ok()`、`err()`、`map()`、`bind()`、`doo()` には PHPDoc の総称型を明示する。

パイプ演算子向けには、インスタンスメソッドだけでなく `Result::bind($callback)`、`Result::map($callback)` のように callable を返す static helper を検討する。これにより `$result |> Result::bind(...) |> Result::map(...)` の形を実現しやすくする。

## Scope
- **In**: `Result` クラス、`LogEntry` インタフェース、ログ collection の保持と結合、Generator ベースの `Result::doo()`、PHPStan を意識した PHPDoc、PHPUnit 単体テスト
- **Out**: Laravel / Monolog へログ出力する interpreter 実装、具体的な `CustomLogEntry` 実装、既存サービス層への大規模適用、PHPStan / Larastan 自体の導入

## Boundary Candidates
- `app/Support/Result.php`: 成功値・失敗値・ログ collection を扱う横断ユーティリティ
- `app/Contracts/LogEntry.php`: ログエントリとして解釈可能な値の契約
- `tests/Unit/Support/ResultTest.php`: `Result` の純粋ロジックを検証する PHPUnit テスト

## Out of Boundary
- `logInterpreter->run($logs)` の具象実装
- Laravel の `Log` ファサードや Monolog handler への変換
- ドメイン固有のログエントリ class 群
- 既存コード全体を Result ベースに書き換える移行
- PHP 8.6 の部分適用構文に依存した実装

## Upstream / Downstream
- **Upstream**: PHP 8.5、Laravel 13、`Illuminate\Support\Collection`、PHP Generator、PHPDoc 総称型
- **Downstream**: 将来のサービス層戻り値、リポジトリ戻り値、ログ interpreter、PHPStan / Larastan 導入時の型検証

## Existing Spec Touchpoints
- **Extends**: なし
- **Adjacent**: 将来作成されるサービス層・ログ処理・静的解析整備の spec

## Constraints
プロジェクトのコマンドは Sail 経由で実行する。PHP ファイルを変更する場合は Pint を実行する。

pipe operator を実コード例として扱う場合、`composer.json` の PHP 制約が現状の `^8.3` のままだと PHP 8.3 環境でパースできないため、実装タスクでは PHP 8.5 を前提にするか、pipe をドキュメント上の利用例に留める判断が必要である。

`Result::doo()` は PHP の構文上、arrow function ではなく `function (): Generator { ... }` 形式を使う。`yield $users = ...` のような擬似構文は PHP で直接表現できないため、実装設計では `yield $this->userRepo->getList()` の戻り値を受け取る書き方に調整する。
