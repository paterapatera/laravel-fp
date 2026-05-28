# Research & Design Decisions: functional-result

---
**Purpose**: functional-result の discovery 結果と設計判断の根拠を記録する。
---

## Summary
- **Feature**: functional-result
- **Discovery Scope**: New Feature（グリーンフィールド共通ユーティリティ）
- **Key Findings**:
  - 既存コードベースに `Result` / `LogEntry` は未実装。`app/Support/` と `app/Contracts/` への新規配置が自然
  - PHP 8.5 のパイプ演算子 `|>` は単一引数 callable への左から右の合成に適するが、`composer.json` の PHP 制約は `^8.3` のまま
  - 外部 Result ライブラリは Writer（ログ collection 結合）要件を満たさないため、プロジェクト内ビルドを採用

## Research Log

### 既存コードベース分析
- **Context**: Extension ではなく新規ユーティリティ。統合点の把握が必要
- **Sources Consulted**: `app/` 配下、`composer.json`、`docs/specs/functional-result/brief.md`
- **Findings**:
  - Laravel スケルトン段階（`User` モデル、薄い Controller のみ）
  - `app/Support/`、`app/Contracts/` は未作成
  - テストは `tests/Unit/` / `tests/Feature/` が存在、`tests/Unit/Support/` は新規
- **Implications**: 既存パターンへの依存は少なく、brief で示されたファイル配置をそのまま採用可能

### PHP 8.5 パイプ演算子
- **Context**: Requirement 3.5（パイプ演算子での合成）
- **Sources Consulted**: [PHP Manual: Functional operators](https://www.php.net/manual/en/language.operators.functional.php)、[PHP.Watch: pipe operator](https://php.watch/versions/8.5/pipe-operator)
- **Findings**:
  - `|>` は左辺の値を右辺の単一引数 callable の第 1 引数として渡す
  - チェーンは左結合。arrow function は括弧必須
  - 複数必須引数の関数はパイプ右辺に置けない
- **Implications**: `Result::map($fn)` / `Result::bind($fn)` のように `callable(Result): Result` を返す static ヘルパーを設計に含める。実装コード内のパイプ例は PHP 8.5 前提とし、CI の PHP 制約は実装タスクで `^8.5` へ上げるか、パイプを PHPDoc 例に留める判断を design に明記

### PHPStan 総称型
- **Context**: Requirement 6（静的解析しやすい公開契約）
- **Sources Consulted**: [PHPStan: Generics in PHP](https://phpstan.org/blog/generics-in-php-using-phpdocs)、[GitHub Discussion #10667](https://github.com/phpstan/phpstan/discussions/10667)
- **Findings**:
  - `@template TValue` / `@template TError` で成功値・失敗理由を分離するのが一般的
  - `ok()` では未使用側に `never` を指定できる（例: `Result<int, never>`）
  - `@template-covariant` は callable 引数位置で variance 問題を起こしやすい（brief の方針と一致）
- **Implications**: 単一 `Result` クラス + 不変 `@template` を採用。`map` / `bind` / `doo` に明示的 PHPDoc を付与

### Build vs Adopt（外部ライブラリ）
- **Context**: Result/Either の既存実装調査
- **Sources Consulted**: [jsoizo/php-result](https://github.com/jsoizo/php-result)、[skie/ROP](https://github.com/skie/ROP)、[JustSteveKing/result](https://github.com/JustSteveKing/result)
- **Findings**:
  - いずれも Either 型の map/bind は提供するが、**LogEntry collection の保持・順序付き結合**は標準機能に含まれない
  - `binding()` / Generator パターンは jsoizo が参考になるが API 名・ログ結合仕様が本 spec と不一致
  - 新規依存を増やさず Laravel `Collection` と統合する方が steering に合致
- **Implications**: カスタム `App\Support\Result` をビルド。Either + Writer の合成要件が採用拒否の主因

### Generator ベース doo
- **Context**: Requirement 5
- **Sources Consulted**: brief.md、jsoizo `Result::binding` パターン
- **Findings**:
  - PHP では `yield $repo->getList()` で Result を受け取る（擬似代入構文は不可）
  - arrow function ではなく `function (): Generator { ... }` が必要
  - 失敗時は Generator の残り評価を中断し、評価済み Result のログのみ結合
- **Implications**: `Result::doo(callable(): Generator): Result` とし、各 `yield` は `Result` インスタンス必須。最終 yield の Result をベースに、先行ステップのログを `concat` で付与して返す

## Architecture Pattern Evaluation

| Option | Description | Strengths | Risks / Limitations | Notes |
|--------|-------------|-----------|---------------------|-------|
| 単一 immutable Result クラス | ok/err 状態 + Collection ログを 1 クラスで表現 | API が単純、brief と一致 | クラスが肥大化しうる | **採用** |
| Ok/Err サブクラス分離 | PHPStan sealed パターン | 型の表現力が高い | ファイル数増、ログ結合ロジックが分散 | 今回のスコープでは過剰 |
| 外部 ROP ライブラリ採用 | composer 依存追加 | 実装工数削減 | Writer 要件非対応、API 不一致 | **不採用** |

## Design Decisions

### Decision: 単一 Result クラス + マーカー LogEntry
- **Context**: Either + Writer の合成、境界の明確化（Req 7）
- **Alternatives Considered**:
  1. Ok/Err 継承階層
  2. 外部ライブラリラップ
- **Selected Approach**: `App\Support\Result`（private コンストラクタ）と `App\Contracts\LogEntry`（マーカー interface）
- **Rationale**: 要件のログ結合を 1 箇所に集約し、呼び出し側 API を brief の `ok` / `err` / `doo` に揃える
- **Trade-offs**: PHPStan の判別は `isOk()` / `isErr()` ベース。sealed 階層より表現力は劣るが PHPDoc で補完
- **Follow-up**: 実装時に `never` 型引数のファクトリ PHPDoc を検証

### Decision: 失敗理由は Throwable に限定しない
- **Context**: Req 1.2「失敗理由」、brief の `err($reason)`
- **Selected Approach**: `@template TError` で任意型を許容（string、array、Throwable 等）
- **Rationale**: ドメインエラーコードを例外に包まず返せる
- **Trade-offs**: `get()` は成功値のみ対象。失敗理由取得は `error()` 等の専用 API で明示

### Decision: ログ結合は Collection::concat
- **Context**: Req 4.4、5.3、5.4
- **Selected Approach**: `Illuminate\Support\Collection<int, LogEntry>` を保持し、`map` / `bind` / `doo` で評価済み分を順序保持で `concat`
- **Rationale**: Laravel 標準、steering 準拠
- **Trade-offs**: `Collection` への依存が Result に入る（許容済み依存）

### Decision: パイプ向け static ヘルパー
- **Context**: Req 3.5
- **Selected Approach**: instance の `map` / `bind` と同名の static メソッドが `callable(Result): Result` を返す（PHP では static / instance 同名を許容）
- **Rationale**: `$result |> Result::bind($fn) |> Result::map($g)` が可能
- **Follow-up**: composer PHP 制約とテスト実行 PHP バージョンの整合

### Synthesis: 一般化
- **Log merging** は Req 4 と Req 5 の共通関心事 → private `mergeLogs(Result...): Collection` に集約（設計上の内部責務、実装詳細）
- **Short-circuit** は `map` / `bind` / `doo` で共通 → 失敗時はコールバック未実行・Generator 未再開

### Synthesis: 簡素化
- Log interpreter、具象 LogEntry、PHPStan パッケージ導入はスコープ外のため設計に含めない
- `mapError` / `tap` / `accumulate` 等は要件にないため公開 API に含めない

## Risks & Mitigations
- **PHP 8.3 vs 8.5 ギャップ** — パイプ構文をテストから除外するか、`composer.json` を `^8.5` に更新（実装タスクで決定）
- **PHPStan 未導入** — PHPDoc を将来 Larastan 導入に備えて記述。導入自体は別 spec
- **doo の誤用** — Result 以外を yield した場合は `InvalidArgumentException` で即失敗（Req 5.5）
- **get() の例外** — 失敗状態で `LogicException` または専用例外をスローし、Req 2.2 の「明確に示す」を満たす

## References
- [PHP 8.5 Pipe Operator (PHP.Watch)](https://php.watch/versions/8.5/pipe-operator)
- [PHPStan: Generics in PHP using PHPDocs](https://phpstan.org/blog/generics-in-php-using-phpdocs)
- [jsoizo/php-result](https://github.com/jsoizo/php-result) — Generator binding の参考（採用せず）
