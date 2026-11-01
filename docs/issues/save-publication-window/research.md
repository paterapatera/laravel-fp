# Research

## Questions

- RQ1: 登録は送られた期間を今どう扱うか
- RQ2: 登録の失敗は画面へどう届くか
- RQ3: 開始と終了の列と、アプリの時刻は何か

## Findings

### RQ1

- `AnnouncementRegistrar::register` は題名、要約、本文だけを受ける。 (source: app/Announcements/AnnouncementRegistrar.php:14)
- 保存する下書きの `starts_at` と `ends_at` は常に null である。 (source: app/Announcements/AnnouncementRegistrar.php:30)
- 期間付きの登録成功テストは、終了が開始より前でも下書きを一件作り、開始と終了は null のままである。 (source: tests/Feature/AnnouncementRegistrationStoreTest.php:89)
- 要件は、空の期間、0 以上 23 以下の時までの期間、片方だけ、逆転、時までの日時でない値を分けて扱う。 (source: REQ-ANR-15, REQ-ANR-NEW-1, REQ-ANR-NEW-2, REQ-ANR-NEW-4, REQ-ANR-NEW-6)

### RQ2

- 失敗時は `Result` の文字列リストを、登録画面へのリダイレクトのエラー文言に変える。題名、要約、本文の入力文字は `withInput` で残す。 (source: app/Http/Controllers/AnnouncementRegistrationController.php:40)
- 画面のエラーは項目ごとの文言で、題名が無いときは「題名がありません」である。 (source: app/Http/Controllers/AnnouncementRegistrationController.php:98)
- 登録画面に開始と終了の入力は無い。 (source: resources/views/announcements/register.blade.php:17)

### RQ3

- `announcements.starts_at` と `announcements.ends_at` は nullable な timestamp として既にある。 (source: database/migrations/2026_10_27_125305_create_announcements_table.php:20)
- アプリの既定タイムゾーンは UTC である。 (source: config/app.php:68)
- 時までの日時の時は UTC のその時であり、整数は 0 以上 23 以下である。 (source: docs/issues/save-publication-window/requirements.md:85)

## Options

### D1: 期間を判定する場所
- Drivers: 不正な期間を保存しない
- Related: RQ1, RQ2

| Option | Pros | Cons |
|---|---|---|
| AnnouncementRegistrar が期間も判定する | 保存の直前で一箇所が拒む | 登録クラスが受け取る文字列が増える |
| コントローラの検証が先に期間を拒む | 画面のエラー袋に近い | 登録クラスを直接呼ぶと不正な期間を保存し得る |

### D2: 失敗リストの形
- Drivers: 不足、不正、逆転を同じ登録結果で返す
- Related: RQ2

| Option | Pros | Cons |
|---|---|---|
| 失敗は項目名と種類の文字列リスト | 今の `list<string>` に種類を足せる | 文字列の意味を呼び出し側と揃える |
| フォームリクエストだけがエラーを持つ | 画面は Laravel の検証に任せる | 登録クラスは期間の失敗を返せない |

## Conclusions

- D1: AnnouncementRegistrar が期間も判定する -> No ADR (登録の能力だけで、判定の場所は後から移せる)
- D2: 失敗は項目名と種類の文字列リスト -> No ADR (呼び出し元は登録コントローラだけで、リストの形は後から変えられる)

# Delta

追加する決定は無い。
