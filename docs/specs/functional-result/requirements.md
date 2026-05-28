# Requirements Document

## Introduction
PHP の関数型機能の拡張に備え、アプリケーション開発者が成功値・失敗値・処理ログを一貫した値として扱える `Result` API を提供する。

この機能は、Either モナドのような成功/失敗の分岐と、Writer モナドのようなログ蓄積を合わせた開発者向けユーティリティである。サービス層やリポジトリ層の戻り値として使えるようにし、チェーン処理、早期中断、ログ collection の結合、静的解析しやすい型契約を実現する。

## Boundary Context
- **In scope**: `Result` の成功/失敗状態、値取得、変換、合成、ログ保持、ログ結合、`doo` による逐次合成、`LogEntry` の契約、静的解析しやすい公開 API
- **Out of scope**: ログを実際に出力する interpreter、Laravel / Monolog への変換、具体的なログエントリ実装、既存コード全体の Result 化、静的解析ツール自体の導入
- **Adjacent expectations**: 将来のサービス層・リポジトリ層・ログ interpreter は、この機能が返す `Result` とログ collection を利用できることを期待する

## Requirements

### Requirement 1: Result の生成と状態判定
**Objective:** As an アプリケーション開発者, I want 成功または失敗を表す Result を明示的に生成して判定できる, so that 例外や null に依存せず処理結果を扱える

#### Acceptance Criteria
1. When 開発者が成功値を指定して Result を生成する, the Result API shall 成功状態の Result として値を保持する
2. When 開発者が失敗理由を指定して Result を生成する, the Result API shall 失敗状態の Result として失敗理由を保持する
3. When 開発者が成功状態を判定する, the Result API shall 成功状態の Result だけを成功として報告する
4. When 開発者が失敗状態を判定する, the Result API shall 失敗状態の Result だけを失敗として報告する
5. The Result API shall 1 つの Result で成功値と失敗理由を同時に有効な状態として扱わない

### Requirement 2: 値の取得と代替値
**Objective:** As an アプリケーション開発者, I want Result から値を安全に取り出せる, so that 成功時の値利用と失敗時のフォールバックを明確に分けられる

#### Acceptance Criteria
1. When 開発者が成功状態の Result から値を取得する, the Result API shall 保持している成功値を返す
2. If 開発者が失敗状態の Result から成功値を直接取得しようとする, then the Result API shall 成功値が存在しないことを明確に示す
3. When 開発者が代替値を指定して成功状態の Result から値を取得する, the Result API shall 保持している成功値を返す
4. When 開発者が代替値を指定して失敗状態の Result から値を取得する, the Result API shall 指定された代替値を返す

### Requirement 3: Result の変換と合成
**Objective:** As an アプリケーション開発者, I want 成功時の値だけを変換または次の Result に合成できる, so that 関数型のチェーン処理で失敗分岐を局所化できる

#### Acceptance Criteria
1. When 開発者が成功状態の Result に値変換を適用する, the Result API shall 変換後の値を持つ成功状態の Result を返す
2. When 開発者が失敗状態の Result に値変換を適用する, the Result API shall 元の失敗理由を保持した失敗状態の Result を返す
3. When 開発者が成功状態の Result に Result を返す処理を合成する, the Result API shall 合成処理が返した Result を返す
4. When 開発者が失敗状態の Result に Result を返す処理を合成する, the Result API shall 合成処理を実行せず元の失敗状態の Result を返す
5. Where パイプ演算子を使った合成が利用される, the Result API shall Result を 1 引数として受け取れる callable 形式で変換と合成を表現できる

### Requirement 4: ログ collection の保持と結合
**Objective:** As an アプリケーション開発者, I want 各 Result に処理ログを付随させられる, so that 計算結果と診断情報を同じ流れで扱える

#### Acceptance Criteria
1. When 開発者がログ collection を指定して成功状態の Result を生成する, the Result API shall 成功値とともにそのログ collection を保持する
2. When 開発者がログ collection を指定して失敗状態の Result を生成する, the Result API shall 失敗理由とともにそのログ collection を保持する
3. When 開発者がログ collection を省略して成功状態の Result を生成する, the Result API shall 空のログ collection を保持する
4. When 複数の Result が変換または合成される, the Result API shall 評価済みの Result に含まれるログ collection を順序を保って結合する
5. The Result API shall ログ collection 内の要素を LogEntry 契約に従う値として扱う

### Requirement 5: doo による逐次処理
**Objective:** As an アプリケーション開発者, I want 複数の Result を逐次的に評価して最初の失敗で中断できる, so that ネストした分岐を書かずに処理フローを表現できる

#### Acceptance Criteria
1. When 開発者が複数の成功状態の Result を doo で逐次評価する, the Result API shall 最終的な成功状態の Result を返す
2. When doo の逐次評価中に失敗状態の Result が現れる, the Result API shall 以降の評価を中断して失敗状態の Result を返す
3. When doo が成功状態で完了する, the Result API shall 評価済みのすべての Result のログ collection を順序を保って結合する
4. When doo が失敗状態で中断する, the Result API shall 中断時点までに評価済みの Result のログ collection を順序を保って結合する
5. If doo に Result ではない値が評価対象として渡される, then the Result API shall 不正な利用であることを明確に示す

### Requirement 6: 静的解析しやすい公開契約
**Objective:** As an アプリケーション開発者, I want Result の値型・失敗理由型・ログ型を静的解析で追跡しやすい, so that チェーン処理の型不一致を早い段階で検出できる

#### Acceptance Criteria
1. The Result API shall 成功値の型と失敗理由の型を公開契約として表現する
2. The Result API shall 値変換後の成功値の型を公開契約として表現する
3. The Result API shall Result を返す合成処理後の成功値と失敗理由の型を公開契約として表現する
4. The Result API shall ログ collection の要素が LogEntry 契約に従うことを公開契約として表現する
5. Where 静的解析ツールが公開契約を読む, the Result API shall 可能な範囲で開発者の明示的な型注釈を補助する情報を提供する

### Requirement 7: スコープ境界
**Objective:** As an アプリケーション開発者, I want Result とログ出力の責務境界が明確である, so that 今回の機能を安全に小さく導入できる

#### Acceptance Criteria
1. The Result API shall ログ collection を保持して返す責務を持つ
2. The Result API shall ログを外部のログシステムへ出力する責務を持たない
3. The LogEntry contract shall ログエントリとして扱える値の共通契約を示す
4. The LogEntry contract shall 具体的なログ出力先、ログ形式、永続化方法を要求しない
5. The feature shall 既存のサービス層やリポジトリ層を一括で Result ベースへ移行することを要求しない
