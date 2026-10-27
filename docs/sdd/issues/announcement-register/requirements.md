# Context

## Actors

- 管理者: お知らせの下書きを登録する。
- 管理者でないログイン済みの利用者: 登録できない。
- 未ログインの訪問者: 登録画面を開くとログインへ移る。

## Current behavior

- REQ-ANN-1 は draft を一覧から外す。
- REQ-ANN-10 は一覧と詳細に作成、編集、削除の操作を出さない。
- REQ-ANN-11 はお知らせの変更と削除を拒否する。
- DATA-ANN-1 は題名、要約、本文、状態、開始、終了、作成時刻を持つ。
- REQ-DSH-1 は未ログインの訪問者がダッシュボードまたは詳細を開くとログインを示す。
- [お知らせの閲覧](/specs/announcements/read/) の Scope は、管理画面での作成と編集をその能力の範囲外としている。

## Proposal Coverage

- 管理者権限のある利用者は、お知らせを下書きとして一件追加できる。: REQ-ANN-10, REQ-ANR-NEW-1, REQ-ANR-NEW-3, REQ-ANR-NEW-4, REQ-ANR-NEW-11, REQ-ANR-NEW-12, REQ-ANR-NEW-13, REQ-ANR-NEW-14, REQ-ANR-NEW-15, REQ-ANR-NEW-16, REQ-ANR-NEW-17, REQ-ANR-NEW-18
- 管理者権限の無い利用者は、お知らせを追加できない。: REQ-ANR-NEW-2, REQ-ANR-NEW-5, REQ-ANR-NEW-6, REQ-ANR-NEW-7, REQ-ANR-NEW-8, REQ-ANR-NEW-9, REQ-ANR-NEW-10

## Assumptions

- 管理者権限を持つ利用者は、この課題の外で用意されている。

# Open Questions

- [x] Q1: 登録画面へ来る入口はどれか
  - [5] 管理者のリンクとURL (chosen)
  - [4] URL直開きだけ
  - [3] 管理者のリンクだけ
  Answer: REQ-ANN-10, REQ-ANR-NEW-1, REQ-ANR-NEW-4
- [x] Q2: 未ログインが登録URLを開くと
  - [5] ログインへ移り件数は増えない (chosen)
  - [4] 見つからず件数は増えない
  - [2] 登録画面が出る
  Answer: REQ-ANR-NEW-5, REQ-ANR-NEW-6, REQ-ANR-NEW-9, REQ-ANR-NEW-10
- [x] Q3: 管理者以外が登録URLを開くと
  - [5] 画面は出ず件数は増えない (chosen)
  - [4] 画面は出るが保存しない
  - [2] 下書きを追加できる
  Answer: REQ-ANR-NEW-7, REQ-ANR-NEW-8, REQ-ANR-NEW-9, REQ-ANR-NEW-10
- [x] Q4: 登録に成功したとき見せるものは
  - [5] 保存した下書きの題名 (chosen)
  - [4] ダッシュボードに戻る
  - [3] 題名と本文
  Answer: REQ-ANR-NEW-16, REQ-ANR-NEW-17
- [x] Q5: 題名や本文が空の登録は
  - [5] 保存せず不足を示す (chosen)
  - [4] 題名だけ必須で他は空可
  - [3] 空の下書きを残す
  Answer: REQ-ANR-NEW-11, REQ-ANR-NEW-12, REQ-ANR-NEW-13
- [x] Q6: 管理者以外に見せる結果は
  - [5] 見つからない結果 (chosen)
  - [4] ダッシュボード
  - [3] 権限が無いと示す
  Answer: REQ-ANR-NEW-7, REQ-ANR-NEW-8
- [x] Q7: 要約が空の登録は
  - [5] 保存せず不足を示す (chosen)
  - [4] 空のまま下書きを残す
  Answer: REQ-ANR-NEW-11, REQ-ANR-NEW-12
- [x] Q8: 開始や終了が空の登録は
  - [5] 空のまま下書きを残す (chosen)
  - [4] 保存せず不足を示す
  Answer: REQ-ANR-NEW-15
- [x] Q9: 不足のあと再入力すると
  - [5] 入力を残して再登録できる (chosen)
  - [4] 空の画面からやり直す
  Answer: REQ-ANR-NEW-13, REQ-ANR-NEW-14
- [x] Q10: 成功画面の題名は
  - [5] 入力の文字をそのまま出す (chosen)
  - [2] HTMLとして描画する
  Answer: REQ-ANR-NEW-16
- [x] Q11: 期間を付けて登録したとき残るものは
  - [5] 開始も終了も空 (chosen)
  - [4] 入力した期間
  Answer: REQ-ANR-NEW-15
- [x] Q12: 成功した結果を再送すると
  - [5] 下書きは一件のまま (chosen)
  - [3] 下書きがもう一件増える
  Answer: REQ-ANR-NEW-18
- [x] Q13: 空白だけの題名や本文は
  - [5] 空と同じく保存しない (chosen)
  - [3] 下書きとして残す
  Answer: Definitions in /specs/announcements/register, REQ-ANR-NEW-11
- [x] Q14: 登録へのリンクを出す画面は
  - [5] ダッシュボードだけ (chosen)
  - [4] ダッシュボードと詳細
  Answer: REQ-ANN-10, REQ-ANR-NEW-1, REQ-ANR-NEW-2, REQ-ANR-NEW-3

# Delta

## Coverage

| capability | spec.md | interfaces.md | data.md | quality.md |
|---|---|---|---|---|
| /specs/announcements/read | changed | no change: 境界の契約は変わらない | no change: 保存項目は変わらない | no change: 数値目標は変わらない |
| /specs/announcements/register | changed | no change: 他能力が呼ぶ契約は増えない | no change: 保存項目は DATA-ANN-1 のまま | no change: 数値目標は置かない |

## New capability: /specs/announcements/register

- code: ANR
- title: お知らせの登録
- description: 管理者がお知らせの下書きを一件追加する。
- tags: [announcements]

## /specs/announcements/read/spec.md

### MODIFIED

#### REQ-ANN-10: No editing controls
- base: eba2f340a71d
- change: 作成の入口はお知らせの登録へ移し、一覧と詳細では編集と削除を出さない。

一覧または詳細を示すとき、システムはお知らせの編集と削除の操作を出さないままにしなければならない。

##### Scenario: List has no editing controls

- **前提** ログイン済みの利用者と、公開対象がある
- **もし** ダッシュボードを開く
- **ならば** 編集と削除の操作は無い

##### Scenario: Detail has no editing controls

- **前提** ログイン済みの利用者と、公開対象がある
- **もし** その詳細を開く
- **ならば** 編集と削除の操作は無い

## /specs/announcements/register/spec.md

### SECTIONS

#### Purpose

管理者がお知らせを下書きとして追加できるようにする。

#### Scope

登録の入口、下書きの追加、空白だけの入力の拒否、成功した結果の再送。保存済みのお知らせの編集と削除、管理者権限の付与、公開期間の保存は範囲外である。一覧と詳細は [お知らせの閲覧](/specs/announcements/read/) に属する。

#### Definitions

登録画面は、新しいお知らせの題名、要約、本文を入力する画面である。空白だけとは、文字が無いか、空白文字だけであることをいう。題名、要約、本文の空白だけは空と同じである。

#### Related

- [お知らせの閲覧](/specs/announcements/read/)
- [ダッシュボードへのアクセス](/specs/dashboard/access/)

### ADDED

#### REQ-ANR-NEW-1: Show the registration link

管理者がダッシュボードを開いたとき、システムは登録画面へのリンクを示さなければならない。

##### Scenario: Admin opens the dashboard

- **前提** 管理者
- **もし** ダッシュボードを開く
- **ならば** 登録画面へのリンクが出る

#### REQ-ANR-NEW-2: Hide the link from other users

管理者でないログイン済みの利用者がダッシュボードを開いたとき、システムは登録画面へのリンクを出さないままにしなければならない。

##### Scenario: Non-admin opens the dashboard

- **前提** 管理者でないログイン済みの利用者
- **もし** ダッシュボードを開く
- **ならば** 登録画面へのリンクは出ない

#### REQ-ANR-NEW-3: Hide the link outside the dashboard

ダッシュボード以外を示している間、システムは登録画面へのリンクを出さないままにしなければならない。

##### Scenario: Admin opens a detail

- **前提** 管理者と、公開対象のお知らせ
- **もし** その詳細を開く
- **ならば** 登録画面へのリンクは出ない

##### Scenario: Admin opens the welcome page

- **前提** 管理者
- **もし** ウェルカムを開く
- **ならば** 登録画面へのリンクは出ない

##### Scenario: Registration succeeds

- **前提** 管理者と、空白以外の題名、要約、本文
- **もし** 登録に成功する
- **ならば** その結果に登録画面へのリンクは出ない

#### REQ-ANR-NEW-4: Open an empty registration form

管理者が登録画面を開いたとき、システムは空の登録入力を示さなければならない。

##### Scenario: Open from the dashboard link

- **前提** 管理者と、保存済みのお知らせ
- **もし** ダッシュボードの登録画面へのリンクを開く
- **ならば** 題名、要約、本文の入力が空である
- **かつ** 保存済みの題名は出ない

##### Scenario: Open the registration URL

- **前提** 管理者と、保存済みのお知らせ
- **もし** 登録画面の URL を開く
- **ならば** 題名、要約、本文の入力が空である
- **かつ** 保存済みの題名は出ない

#### REQ-ANR-NEW-5: Send guests who open registration to login

未ログインの訪問者が登録画面を開いたとき、システムはログインを示さなければならない。

##### Scenario: Guest opens the registration URL

- **前提** ログインしていない訪問者と、保存済みのお知らせ
- **もし** 登録画面の URL を開く
- **ならば** ログインが出る
- **かつ** その題名、要約、本文は出ない

#### REQ-ANR-NEW-6: Send guests who submit registration to login

未ログインの訪問者が登録を求めた場合、システムはログインを示さなければならない。

##### Scenario: Guest submits registration

- **前提** ログインしていない訪問者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** ログインが出る
- **かつ** その題名は出ない

#### REQ-ANR-NEW-7: Hide registration from a non-admin

管理者でないログイン済みの利用者が登録画面を開いたとき、システムは本文の無い見つからない結果を示さなければならない。

##### Scenario: Non-admin opens the registration URL

- **前提** 管理者でないログイン済みの利用者
- **もし** 登録画面の URL を開く
- **ならば** 見つからない結果が出る
- **かつ** 登録入力は出ない
- **かつ** 本文は出ない

#### REQ-ANR-NEW-8: Hide a non-admin submission

管理者でないログイン済みの利用者が登録を求めた場合、システムは本文の無い見つからない結果を示さなければならない。

##### Scenario: Non-admin submits registration

- **前提** 管理者でないログイン済みの利用者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** 見つからない結果が出る
- **かつ** その題名は出ない
- **かつ** 本文は出ない

#### REQ-ANR-NEW-9: Opening registration adds nothing for others

管理者以外が登録画面を開いた場合、システムはお知らせを追加しないままにしなければならない。

##### Scenario: Guest opens registration

- **前提** ログインしていない訪問者と、お知らせが一件ある
- **もし** 登録画面の URL を開く
- **ならば** お知らせは一件のままである

##### Scenario: Non-admin opens registration

- **前提** 管理者でないログイン済みの利用者と、お知らせが一件ある
- **もし** 登録画面の URL を開く
- **ならば** お知らせは一件のままである

#### REQ-ANR-NEW-10: Submission adds nothing for others

管理者以外が登録を求めた場合、システムはお知らせを追加しないままにしなければならない。

##### Scenario: Guest submits registration

- **前提** ログインしていない訪問者と、お知らせが一件ある
- **もし** 空白以外の題名、要約、本文で登録を求める
- **ならば** お知らせは一件のままである

##### Scenario: Non-admin submits registration

- **前提** 管理者でないログイン済みの利用者と、お知らせが一件ある
- **もし** 空白以外の題名、要約、本文で登録を求める
- **ならば** お知らせは一件のままである

#### REQ-ANR-NEW-11: Reject blank text

題名、要約、本文のいずれかが空白だけの登録を管理者が求めた場合、システムはお知らせを追加しないままにしなければならない。

##### Scenario: Blank title

- **前提** 管理者と、お知らせが一件も無い
- **もし** 題名が空で、要約と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

##### Scenario: Blank summary

- **前提** 管理者と、お知らせが一件も無い
- **もし** 要約が空で、題名と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

##### Scenario: Blank body

- **前提** 管理者と、お知らせが一件も無い
- **もし** 本文が空で、題名と要約に文字がある登録を求める
- **ならば** お知らせは一件も無い

##### Scenario: Whitespace title

- **前提** 管理者と、お知らせが一件も無い
- **もし** 題名が空白だけで、要約と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

##### Scenario: Whitespace summary

- **前提** 管理者と、お知らせが一件も無い
- **もし** 要約が空白だけで、題名と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

##### Scenario: Whitespace body

- **前提** 管理者と、お知らせが一件も無い
- **もし** 本文が空白だけで、題名と要約に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### REQ-ANR-NEW-12: Indicate blank fields

題名、要約、本文のいずれかが空白だけの登録を管理者が求めた場合、システムは空白だけの項目を示さなければならない。

##### Scenario: Missing summary is indicated

- **前提** 管理者と、文字のある題名と本文、空の要約
- **もし** 登録を求める
- **ならば** 要約の不足が示される
- **かつ** 題名の不足は示されない

##### Scenario: Missing title and body are indicated

- **前提** 管理者と、空の題名、文字のある要約、空の本文
- **もし** 登録を求める
- **ならば** 題名の不足と本文の不足が示される

#### REQ-ANR-NEW-13: Keep entered characters

不足を示している間、システムは入力済みの文字をそのまま残さなければならない。

##### Scenario: Markup stays as characters

- **前提** 管理者と、題名 `<b>注意</b>`、空の要約、文字のある本文
- **もし** 登録を求める
- **ならば** `<b>注意</b>` の文字が残る
- **かつ** 本文の文字が残る

#### REQ-ANR-NEW-14: Add one draft

管理者が空白以外の題名、要約、本文で登録したとき、システムは状態が draft のお知らせを一件追加しなければならない。

##### Scenario: First draft

- **前提** 管理者と、お知らせが一件も無く、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** 状態が draft のお知らせが一件増える
- **かつ** ダッシュボードの一覧にその題名は出ない

##### Scenario: Retry after a blank summary

- **前提** 管理者と、要約が空のため追加されなかった題名と本文
- **もし** 要約を補って登録を求める
- **ならば** 状態が draft のお知らせが一件増える

##### Scenario: Second registration

- **前提** 管理者と、登録済みの下書きが一件ある
- **もし** 別の空白以外の題名、要約、本文で登録を求める
- **ならば** 状態が draft のお知らせがもう一件増える

#### REQ-ANR-NEW-15: Leave the window empty

登録が成功したとき、システムは開始と終了が空のお知らせを残さなければならない。

##### Scenario: No window submitted

- **前提** 管理者と、空白以外の題名、要約、本文
- **もし** 開始と終了を付けずに登録を求める
- **ならば** そのお知らせの開始と終了は空である

##### Scenario: Submitted window is dropped

- **前提** 管理者と、空白以外の題名、要約、本文と、終了が開始より前の期間
- **もし** その期間を付けて登録を求める
- **ならば** そのお知らせの開始と終了は空である

#### REQ-ANR-NEW-16: Show the title as entered

登録が成功したとき、システムは保存した題名の文字をそのまま示さなければならない。

##### Scenario: Title characters stay literal

- **前提** 管理者と、題名 `<b>注意</b>`、空白以外の要約と本文
- **もし** 登録を求める
- **ならば** `<b>注意</b>` の文字が出る

#### REQ-ANR-NEW-17: Hide summary and body after success

登録が成功したとき、システムは要約と本文を出さないままにしなければならない。

##### Scenario: Summary and body stay off the result

- **前提** 管理者と、要約にも本文にも題名に無い文字がある
- **もし** 登録を求める
- **ならば** その要約は出ない
- **かつ** その本文は出ない

#### REQ-ANR-NEW-18: Replaying success adds nothing

登録の成功を再送した場合、システムはその題名の下書きを一件のままにしなければならない。

##### Scenario: Replay the success result

- **前提** 管理者と、登録の成功で増えた題名の下書きが一件
- **もし** その成功の結果を再送する
- **ならば** その題名の下書きは一件のままである

## /steering/product.md

### SECTIONS

#### Stakeholders and Users
- base: f3d66e4836c0

- 開発者は Sail 上でアプリケーションを変更し、Pest と Larastan と Pint で確認する。
- ブラウザの訪問者は `/` のウェルカムページを見る。未ログインの訪問者はそこからログインへ進める。
- 用意された利用者はログインし、ダッシュボードでお知らせを読む。
- 管理者はログインし、ダッシュボードからお知らせの下書きを登録する。
- 監視は `/up` でプロセスの生存を確認する。
- ローカルのシードは `test@example.com` の利用者を 1 件作る。

#### Operational Scenarios
- base: fa3ab8a31f1e

- 開発者は Sail を起動し、ブラウザで `/` を開く。ウェルカムページは HTTP 200 を返す。
- 監視は `/up` を呼ぶ。
- 開発者はマイグレーションのあと、データベースドライバのセッション、キャッシュ、キューを使う。`composer dev` は HTTP サーバ、`queue:listen`、Pail、Vite を同時に動かす。
- アプリケーションコードは `Result` を関数の戻り値として渡し、付随する `LogEntry` を呼び出し元が解釈する。このログは `LOG_CHANNEL` のアプリケーションログとは別のメモリ上のコレクションである。
- 開発者は `DatabaseSeeder` で確認用の利用者を 1 件作る。
- 用意された利用者はウェルカムのログインから入り、ダッシュボードでお知らせを読む。
- 管理者はダッシュボードからお知らせの下書きを登録する。
- 未ログインの訪問者がダッシュボードまたはお知らせの詳細を開くと、ログインへ移り、お知らせは見えない。
- 利用者はログアウトすると、未認証のウェルカムに戻る。

## /steering/glossary.md

### ADDED

#### 管理者

お知らせの下書きを登録する権限を持つ利用者。
