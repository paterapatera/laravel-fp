# Context

## Actors

- 訪問者: ログインしていない人。
- 利用者: 画面の外で用意されたアカウントを持つ人。
- 運用: 利用者の画面の外でお知らせを保存する人。

## Current behavior

- `routes/web.php:5` は `/` でウェルカムだけを返す。
- `database/seeders/DatabaseSeeder.php:22` は `test@example.com` の利用者を作る。ログインのルートは無い。
- 仕様木はドメインと能力の二段なので、提案の `/specs/dashboard/` は `/specs/dashboard/access`、`/specs/announcements/` は `/specs/announcements/read` に置く。

## Proposal Coverage

- 認証とダッシュボード: REQ-DSH-NEW-1, REQ-DSH-NEW-2, REQ-DSH-NEW-3, REQ-DSH-NEW-4, REQ-DSH-NEW-5, REQ-DSH-NEW-6, REQ-DSH-NEW-7, REQ-DSH-NEW-8, REQ-DSH-NEW-9, REQ-DSH-NEW-10, REQ-DSH-NEW-11, REQ-DSH-NEW-12, REQ-DSH-NEW-13, REQ-DSH-NEW-14, NFR-DSH-NEW-1, design: ログイン画面の実装方式
- お知らせの永続化: DATA-ANN-NEW-1, design: 開発とテストのための Factory と Seeder の構成
- 一覧表示: REQ-ANN-NEW-1, REQ-ANN-NEW-2, REQ-ANN-NEW-3, REQ-ANN-NEW-4, REQ-ANN-NEW-5, REQ-ANN-NEW-9, REQ-ANN-NEW-10
- 詳細表示: REQ-ANN-NEW-6, REQ-ANN-NEW-7, REQ-ANN-NEW-8, REQ-ANN-NEW-11, design: 一件を選ぶ識別子と詳細の URL、見つからない結果のステータスコード。一覧の要約と詳細の本文をプレーンテキストで見せるか、最小限の HTML で見せるか
- 公開データの投入: REQ-ANN-NEW-1, REQ-ANN-NEW-8, design: 管理画面を作らず、Seeder、Artisan、または運用手順で保存する

## Assumptions

- セッションの期限は既存の設定のままで、この変更では決めない。

# Open Questions

- [x] Q1: 一覧に載せるお知らせの範囲はどれか
  Answer: /specs/announcements/read の Definitions
- [x] Q2: 利用者は自分で登録できるか
  Answer: REQ-DSH-NEW-11, REQ-DSH-NEW-12
- [x] Q3: 未ログインで一覧や詳細を開くとどうなるか
  Answer: REQ-DSH-NEW-1
- [x] Q4: 公開対象が無いダッシュボードはどう見えるか
  Answer: REQ-ANN-NEW-5
- [x] Q5: 見られない詳細を開いたときはどうなるか
  Answer: REQ-ANN-NEW-8
- [x] Q6: 一覧の各項目に添える短い文は何か
  Answer: REQ-ANN-NEW-1
- [x] Q7: 開始または終了が空のお知らせはどう扱うか
  Answer: /specs/announcements/read の Definitions
- [x] Q8: ログインに成功した直後はどこを開くか
  Answer: REQ-DSH-NEW-5
- [x] Q9: ウェルカムからログインへ進めるか
  Answer: REQ-DSH-NEW-2
- [x] Q10: 一覧の並び順は何か
  Answer: REQ-ANN-NEW-4
- [x] Q11: 公開開始が同じときの順序は何か
  Answer: REQ-ANN-NEW-4
- [x] Q12: 期間の端の瞬間は公開に含むか
  Answer: /specs/announcements/read の Definitions
- [x] Q13: ログアウトは提供するか
  Answer: REQ-DSH-NEW-9, REQ-DSH-NEW-10
- [x] Q14: ログイン済みがログイン画面を開くとどうなるか
  Answer: REQ-DSH-NEW-8
- [x] Q15: ログイン済みがウェルカムを開くとどうなるか
  Answer: REQ-DSH-NEW-4
- [x] Q16: ログアウトした直後に見せる画面は何か
  Answer: REQ-DSH-NEW-9
- [x] Q17: ログアウトの操作はどこに出すか
  Answer: REQ-DSH-NEW-10
- [x] Q18: ログインに失敗したときに示すことは何か
  Answer: REQ-DSH-NEW-6
- [x] Q19: タイトル、要約、本文のどれかが空ならどう扱うか
  Answer: /specs/announcements/read の Definitions
- [x] Q20: 詳細画面に出すものは何か
  Answer: REQ-ANN-NEW-6, REQ-ANN-NEW-7
- [x] Q21: パスワード再設定は提供するか
  Answer: REQ-DSH-NEW-13, REQ-DSH-NEW-14
- [x] Q22: ログインの連続失敗はどう扱うか
  Answer: NFR-DSH-NEW-1, REQ-DSH-NEW-7
- [x] Q23: 連続失敗はどの単位で数えるか
  Answer: NFR-DSH-NEW-1

# Delta

## Coverage

| capability | spec.md | interfaces.md | data.md | quality.md |
|---|---|---|---|---|
| /specs/announcements/read | changed | no change: 他能力が呼ぶ境界は無く、画面の結果は仕様の要求に書く | changed | no change: 数値目標は無い |
| /specs/dashboard/access | changed | no change: 他能力が呼ぶ境界は無く、画面の結果は仕様の要求に書く | no change: 新しい保存項目は無い | changed |

## New domain: /specs/announcements

- title: お知らせ
- description: 利用者に見せる告知。

## New domain: /specs/dashboard

- title: ダッシュボード
- description: 認証済み利用者の入口。

## New capability: /specs/announcements/read

- code: ANN
- title: お知らせの閲覧
- description: ログイン済みの利用者向けのお知らせの一覧と詳細。
- tags: [announcements]

## New capability: /specs/dashboard/access

- code: DSH
- title: ダッシュボードへのアクセス
- description: 用意された利用者のログイン、ログアウト、ダッシュボードへの到達。
- tags: [dashboard]

## /specs/announcements/read/data.md

### METADATA

- title: お知らせの記録
- description: 利用者が読むお知らせの保存項目。
- tags: [announcements]

### SECTIONS

#### Purpose

公開判定と一覧と詳細に使うお知らせを保持する。

### ADDED

#### DATA-ANN-NEW-1: Announcement

- Classification: Internal
- Retention: 運用が削除するまで

システムは各お知らせを永続化しなければならない。

| Element | Type | Required | Constraints | Description |
|---|---|---|---|---|
| title | string | no | | 一覧と詳細の題名 |
| summary | string | no | | 一覧だけに出す要約 |
| body | string | no | | 詳細だけに出す本文 |
| status | enum(draft, published) | yes | | draft は下書き、published は公開 |
| startsAt | datetime | no | | 公開の開始 |
| endsAt | datetime | no | | 公開の終了 |
| createdAt | datetime | yes | | 作成時刻 |

## /specs/announcements/read/spec.md

### SECTIONS

#### Purpose

ログイン済みの利用者が公開対象のお知らせを一覧と詳細で読めるようにする。

#### Scope

公開対象の判定、一覧、詳細、空の一覧、見つからない詳細。認証の入口は [ダッシュボードへのアクセス](/specs/dashboard/access/) に属する。管理画面での作成と編集は範囲外である。

#### Definitions

公開対象は、次をすべて満たすお知らせである。状態が published である。タイトル、要約、本文のそれぞれに空白以外の文字がある。公開の開始と終了の両方がある。アプリケーションの現在時刻が開始以上かつ終了以下である。開始の瞬間と終了の瞬間は含む。空白だけとは、文字が無いか、空白文字だけであることをいう。

#### Related

- [ダッシュボードへのアクセス](/specs/dashboard/access/)

### ADDED

#### REQ-ANN-NEW-1: List title and summary

ログイン済みの利用者がダッシュボードを開いたとき、システムは各公開対象のタイトルと要約を示さなければならない。

##### Scenario: Visible announcement
- **前提** ログイン済みの利用者と、公開対象のお知らせが一件ある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルと要約が一覧に出る

##### Scenario: Draft is excluded
- **前提** ログイン済みの利用者と、期間内だが draft のお知らせがある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルは一覧に出ない

##### Scenario: Outside the window is excluded
- **前提** ログイン済みの利用者と、published だが現在時刻が終了より後のお知らせがある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルは一覧に出ない

##### Scenario: Inclusive start
- **前提** ログイン済みの利用者と、現在時刻が開始の瞬間と等しい公開対象がある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルが一覧に出る

##### Scenario: Inclusive end
- **前提** ログイン済みの利用者と、現在時刻が終了の瞬間と等しい公開対象がある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルが一覧に出る

##### Scenario: Missing end is excluded
- **前提** ログイン済みの利用者と、published で本文が揃っているが終了が空のお知らせがある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルは一覧に出ない

##### Scenario: Blank summary is excluded
- **前提** ログイン済みの利用者と、要約が空白だけのお知らせがある
- **もし** ダッシュボードを開く
- **ならば** そのタイトルは一覧に出ない

##### Scenario: Blank title is excluded
- **前提** ログイン済みの利用者と、タイトルが空白だけのお知らせがある
- **もし** ダッシュボードを開く
- **ならば** その要約は一覧に出ない

#### REQ-ANN-NEW-2: Same list for every user

ログイン済みの利用者がダッシュボードを開いたとき、システムは他のログイン済み利用者と同じ公開対象を示さなければならない。

##### Scenario: Two users
- **前提** 二人のログイン済み利用者と、公開対象が二件ある
- **もし** それぞれがダッシュボードを開く
- **ならば** 二人の一覧に同じ二件のタイトルが出る

#### REQ-ANN-NEW-3: Hide the body on the list

ログイン済みの利用者がダッシュボードを開いたとき、システムは公開対象の本文を出さないままにしなければならない。

##### Scenario: Body stays off the list
- **前提** ログイン済みの利用者と、本文が要約にもタイトルにも含まれない公開対象がある
- **もし** ダッシュボードを開く
- **ならば** その本文は一覧に出ない

#### REQ-ANN-NEW-4: Sort the list

公開対象が複数ある間、システムは公開開始の新しい順に、開始が同じなら作成の新しい順に、それも同じなら文字符号点の昇順にタイトルを並べなければならない。

##### Scenario: Newer start comes first
- **前提** ログイン済みの利用者と、開始が異なる公開対象が二件ある
- **もし** ダッシュボードを開く
- **ならば** 開始が新しい方が先に出る

##### Scenario: Newer creation breaks a start tie
- **前提** ログイン済みの利用者と、開始が同じで作成時刻が異なる公開対象が二件ある
- **もし** ダッシュボードを開く
- **ならば** 作成が新しい方が先に出る

##### Scenario: Title breaks the remaining tie
- **前提** ログイン済みの利用者と、開始と作成時刻が同じでタイトルの文字符号点が異なる公開対象が二件ある
- **もし** ダッシュボードを開く
- **ならば** 文字符号点が小さいタイトルが先に出る

#### REQ-ANN-NEW-5: Empty list

公開対象が無い間、システムはお知らせが無いことをダッシュボードに示さなければならない。

##### Scenario: Nothing stored
- **前提** ログイン済みの利用者と、お知らせが一件も無い
- **もし** ダッシュボードを開く
- **ならば** お知らせが無いことが示される
- **かつ** 一覧に項目は無い

##### Scenario: Only hidden announcements
- **前提** ログイン済みの利用者と、draft のお知らせだけがある
- **もし** ダッシュボードを開く
- **ならば** お知らせが無いことが示される

#### REQ-ANN-NEW-6: Show title and body

ログイン済みの利用者が公開対象の詳細を開いたとき、システムはそのタイトルと本文を示さなければならない。

##### Scenario: Open a visible announcement
- **前提** ログイン済みの利用者と、公開対象が二件ある
- **もし** そのうち一件の詳細を開く
- **ならば** その一件のタイトルと本文が出る
- **かつ** もう一件の本文は出ない

#### REQ-ANN-NEW-7: Hide the summary on the detail

ログイン済みの利用者が公開対象の詳細を開いたとき、システムは要約を出さないままにしなければならない。

##### Scenario: Summary stays off the detail
- **前提** ログイン済みの利用者と、要約がタイトルにも本文にも含まれない公開対象がある
- **もし** その詳細を開く
- **ならば** その要約は詳細に出ない

#### REQ-ANN-NEW-8: Unreadable detail

ログイン済みの利用者が公開対象でない詳細を開いたとき、システムは本文の無い見つからない結果を示さなければならない。

##### Scenario: Missing announcement
- **前提** ログイン済みの利用者と、該当するお知らせが無い
- **もし** 詳細を開く
- **ならば** 見つからない結果が出る
- **かつ** 本文は出ない

##### Scenario: Draft detail
- **前提** ログイン済みの利用者と、draft のお知らせがある
- **もし** その詳細を開く
- **ならば** 見つからない結果が出る
- **かつ** 本文は出ない

##### Scenario: Before the start
- **前提** ログイン済みの利用者と、現在時刻が開始より前の published のお知らせがある
- **もし** その詳細を開く
- **ならば** 見つからない結果が出る
- **かつ** 本文は出ない

##### Scenario: After the end
- **前提** ログイン済みの利用者と、現在時刻が終了より後の published のお知らせがある
- **もし** その詳細を開く
- **ならば** 見つからない結果が出る
- **かつ** 本文は出ない

##### Scenario: Blank body
- **前提** ログイン済みの利用者と、本文が空白だけの published のお知らせがある
- **もし** その詳細を開く
- **ならば** 見つからない結果が出る

##### Scenario: Missing start
- **前提** ログイン済みの利用者と、開始が空の published のお知らせがある
- **もし** その詳細を開く
- **ならば** 見つからない結果が出る
- **かつ** 本文は出ない

#### REQ-ANN-NEW-9: Open the chosen announcement

ログイン済みの利用者が一覧のお知らせを選んだとき、システムはそのお知らせの詳細を示さなければならない。

##### Scenario: Choose the second item
- **前提** ログイン済みの利用者と、一覧に並ぶ公開対象が二件ある
- **もし** 二件目を選ぶ
- **ならば** 二件目の詳細が出る

#### REQ-ANN-NEW-10: No editing controls

一覧または詳細を示すとき、システムはお知らせの作成、編集、削除の操作を出さないままにしなければならない。

##### Scenario: List has no editing controls
- **前提** ログイン済みの利用者と、公開対象がある
- **もし** ダッシュボードを開く
- **ならば** 作成、編集、削除の操作は無い

##### Scenario: Detail has no editing controls
- **前提** ログイン済みの利用者と、公開対象がある
- **もし** その詳細を開く
- **ならば** 作成、編集、削除の操作は無い

#### REQ-ANN-NEW-11: Reject announcement changes

お知らせの変更または削除を求められた場合、システムは保存済みのタイトル、要約、本文を維持しなければならない。

##### Scenario: Submitted change
- **前提** ログイン済みの利用者と、保存済みの公開対象がある
- **もし** そのタイトルの変更を求める
- **ならば** 保存済みのタイトル、要約、本文は変わらない

## /specs/dashboard/access/quality.md

### METADATA

- title: ログイン失敗の上限
- description: 同一アカウントのログイン失敗回数。
- tags: [dashboard]

### SECTIONS

#### Purpose

同じメールアドレスへのログイン失敗を、短時間に繰り返せないようにする。

### ADDED

#### NFR-DSH-NEW-1: Login failure limit

- Characteristic: Security
- Measure: 入力されたメールアドレスごとの直近1分のログイン失敗回数
- Target: 5回以下
- Condition: 認証情報が一致しなかった試行を、入力されたメールアドレスごとに数える
- Verification: Test

システムは、同一メールアドレスのログイン失敗の上限を直近1分あたり5回にしなければならない。

## /specs/dashboard/access/spec.md

### SECTIONS

#### Purpose

用意された利用者がログインし、ダッシュボードへ到達できるようにする。

#### Scope

ウェルカムからの入口、ログイン、ログアウト、未認証のときの保護画面の拒否。お知らせの中身は [お知らせの閲覧](/specs/announcements/read/) に属する。

#### Definitions

ダッシュボードは、パス `/dashboard` の画面である。ログインは、用意された利用者のメールアドレスとパスワードを受け付ける画面である。保護画面は、ダッシュボードとお知らせの詳細である。未認証の拒否は、セッションを開始せず、アカウントの有無が判別できない表示である。ログイン失敗は、入力されたメールアドレスとパスワードが用意された利用者と一致しない試行である。上限は NFR-DSH-NEW-1 である。

#### Related

- [お知らせの閲覧](/specs/announcements/read/)

### ADDED

#### REQ-DSH-NEW-1: Send guests to login

未ログインの訪問者が保護画面を開いたとき、システムはお知らせの内容が無いログインを示さなければならない。

##### Scenario: Guest opens the dashboard
- **前提** ログインしていない訪問者
- **もし** `/dashboard` を開く
- **ならば** ログインが出る
- **かつ** お知らせのタイトル、要約、本文は出ない

##### Scenario: Guest opens a detail
- **前提** ログインしていない訪問者と、お知らせがある
- **もし** その詳細を開く
- **ならば** ログインが出る
- **かつ** その本文は出ない

##### Scenario: Guest opens a missing detail
- **前提** ログインしていない訪問者と、該当するお知らせが無い
- **もし** お知らせの詳細を開く
- **ならば** ログインが出る
- **かつ** 本文は出ない

#### REQ-DSH-NEW-2: Link to login from the welcome page

未ログインの訪問者がウェルカムを開いたとき、システムはログインへのリンクを示さなければならない。

##### Scenario: Guest opens the welcome page
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** ログインへのリンクが出る

#### REQ-DSH-NEW-3: Keep announcements off the welcome page

ウェルカムを開いたとき、システムはお知らせのタイトル、要約、本文を出さないままにしなければならない。

##### Scenario: Guest welcome
- **前提** ログインしていない訪問者と、お知らせがある
- **もし** ウェルカムを開く
- **ならば** そのタイトル、要約、本文は出ない

##### Scenario: Authenticated welcome
- **前提** ログイン済みの利用者と、お知らせがある
- **もし** ウェルカムを開く
- **ならば** そのタイトル、要約、本文は出ない

#### REQ-DSH-NEW-4: Link to the dashboard from the welcome page

ログイン済みの利用者がウェルカムを開いたとき、システムはダッシュボードへのリンクを示さなければならない。

##### Scenario: Authenticated user opens the welcome page
- **前提** ログイン済みの利用者
- **もし** ウェルカムを開く
- **ならば** ダッシュボードへのリンクが出る

#### REQ-DSH-NEW-5: Enter the requested screen after login

上限内で認証情報が一致するログインに成功したとき、システムは直前に求めた保護画面を、求めた画面が無ければダッシュボードを示さなければならない。

##### Scenario: Login directly
- **前提** 用意された利用者と、保護画面を求める前のログイン
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

##### Scenario: Login after a detail request
- **前提** 用意された利用者と、未ログインのまま求めた、本文を読めるお知らせの詳細
- **もし** 一致する認証情報でログインする
- **ならば** その詳細が出る

##### Scenario: Login after a dashboard request
- **前提** 用意された利用者と、未ログインのまま求めた `/dashboard`
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

##### Scenario: Retry after a mismatch
- **前提** 用意された利用者と、上限内の不一致による未認証の拒否
- **もし** 続けて一致する認証情報でログインする
- **ならば** ダッシュボードが出る

##### Scenario: Login after the limit expires
- **前提** 用意された利用者と、そのメールアドレスへの不一致が5回あるが、どれも直近1分より前である
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

#### REQ-DSH-NEW-6: Hide whether the account exists

認証情報が一致しない場合、システムは未認証の拒否を示さなければならない。

##### Scenario: Unknown email
- **前提** 用意されていないメールアドレス
- **もし** そのメールアドレスでログインする
- **ならば** 未認証の拒否が出る
- **かつ** アカウントが無いことは示されない

##### Scenario: Wrong password
- **前提** 用意された利用者と、一致しないパスワード
- **もし** その利用者のメールアドレスでログインする
- **ならば** 未認証の拒否が出る
- **かつ** 用意されていないメールアドレスのときと同じ拒否である

#### REQ-DSH-NEW-7: Refuse logins past the limit

上限を超えたログインの場合、システムは認証情報が一致しても未認証の拒否を示さなければならない。

##### Scenario: Matching password within the minute
- **前提** 用意された利用者と、そのメールアドレスへの不一致が直近1分に5回ある
- **もし** 一致する認証情報でログインする
- **ならば** 未認証の拒否が出る
- **かつ** ダッシュボードは出ない

##### Scenario: Another email still logs in
- **前提** 二人の用意された利用者と、一方のメールアドレスへの不一致が直近1分に5回ある
- **もし** もう一方が一致する認証情報でログインする
- **ならば** その利用者のセッションが始まる

#### REQ-DSH-NEW-8: Leave the login screen when already authenticated

ログイン済みの利用者がログインを開いたとき、システムはダッシュボードを示さなければならない。

##### Scenario: Authenticated user opens login
- **前提** ログイン済みの利用者
- **もし** ログインを開く
- **ならば** ダッシュボードが出る

#### REQ-DSH-NEW-9: Return to the welcome page after logout

ログアウトしたとき、システムは未認証のウェルカムを示さなければならない。

##### Scenario: Logout from the dashboard
- **前提** ダッシュボードを見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る
- **かつ** お知らせの内容は出ない

##### Scenario: Logout from a detail
- **前提** お知らせ詳細を見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る

##### Scenario: Logout from the welcome page
- **前提** ウェルカムを見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る

##### Scenario: Guest requests logout
- **前提** ログインしていない訪問者
- **もし** ログアウトを求める
- **ならば** 未認証のウェルカムが出る
- **かつ** セッションは始まらない

#### REQ-DSH-NEW-10: Offer logout on authenticated screens

ログイン済みの利用者がウェルカム、ダッシュボード、またはお知らせ詳細を見ている間、システムはログアウトの操作を示さなければならない。

##### Scenario: Logout on the welcome page
- **前提** ログイン済みの利用者
- **もし** ウェルカムを開く
- **ならば** ログアウトの操作が出る

##### Scenario: Logout on the dashboard
- **前提** ログイン済みの利用者
- **もし** ダッシュボードを開く
- **ならば** ログアウトの操作が出る

##### Scenario: Logout on a detail
- **前提** ログイン済みの利用者と、本文を読めるお知らせがある
- **もし** その詳細を開く
- **ならば** ログアウトの操作が出る

#### REQ-DSH-NEW-11: Offer no self-registration

システムは、自己登録の操作が無いログインとウェルカムを示さなければならない。

##### Scenario: Welcome has no registration
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** 自己登録の操作は無い

##### Scenario: Login has no registration
- **前提** ログインしていない訪問者
- **もし** ログインを開く
- **ならば** 自己登録の操作は無い

#### REQ-DSH-NEW-12: Reject self-registration

自己登録を求められた場合、システムは新しい利用者を作らずに拒否しなければならない。

##### Scenario: Direct registration request
- **前提** 利用者のアカウントが一件ある
- **もし** 新しいメールアドレスの自己登録を求める
- **ならば** アカウントは一件のままである
- **かつ** その新しいメールアドレスではログインできない

#### REQ-DSH-NEW-13: Offer no password reset

システムは、パスワード再設定の操作が無いログインとウェルカムを示さなければならない。

##### Scenario: Login has no reset
- **前提** ログインしていない訪問者
- **もし** ログインを開く
- **ならば** パスワード再設定の操作は無い

##### Scenario: Welcome has no reset
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** パスワード再設定の操作は無い

#### REQ-DSH-NEW-14: Reject password reset

パスワード再設定を求められた場合、システムは保存済みのパスワードを維持しなければならない。

##### Scenario: Direct reset request
- **前提** 用意された利用者
- **もし** そのパスワードの再設定を求める
- **ならば** これまでのパスワードでログインできる

## /steering/product.md

### SECTIONS

#### Purpose
- base: 2a464ad060ab

このリポジトリは Laravel アプリケーションである。未ログインの訪問者が最初に見る画面はウェルカムページである。用意された利用者はログインし、認証済みの画面を使える。アプリケーション固有のドメインコードは、処理の成功と失敗を値として運ぶ `Result` と、その値に付随する `LogEntry` である。利用者アカウントのテーブルと Eloquent モデルは用意してある。

振る舞いの詳細は [仕様](/specs/) のドメインに置く。品質の数値目標も仕様に置く。

#### Stakeholders and Users
- base: 9dc513bf8ba3

- 開発者は Sail 上でアプリケーションを変更し、Pest と Larastan と Pint で確認する。
- ブラウザの訪問者は `/` のウェルカムページを見る。未ログインの訪問者はそこからログインへ進める。
- 用意された利用者はログインし、ダッシュボードでお知らせを読む。
- 監視は `/up` でプロセスの生存を確認する。
- ローカルのシードは `test@example.com` の利用者を 1 件作る。

#### Operational Scenarios
- base: 7e4a6f051659

- 開発者は Sail を起動し、ブラウザで `/` を開く。ウェルカムページは HTTP 200 を返す。
- 監視は `/up` を呼ぶ。
- 開発者はマイグレーションのあと、データベースドライバのセッション、キャッシュ、キューを使う。`composer dev` は HTTP サーバ、`queue:listen`、Pail、Vite を同時に動かす。
- アプリケーションコードは `Result` を関数の戻り値として渡し、付随する `LogEntry` を呼び出し元が解釈する。このログは `LOG_CHANNEL` のアプリケーションログとは別のメモリ上のコレクションである。
- 開発者は `DatabaseSeeder` で確認用の利用者を 1 件作る。
- 用意された利用者はウェルカムのログインから入り、ダッシュボードでお知らせを読む。
- 未ログインの訪問者がダッシュボードまたはお知らせの詳細を開くと、ログインへ移り、お知らせは見えない。
- 利用者はログアウトすると、未認証のウェルカムに戻る。

## /steering/glossary.md

### ADDED

#### お知らせ

運用が保存し、ログイン済みの利用者が読む告知。

#### ダッシュボード

ログイン済みの利用者がお知らせの一覧を見る認証済みの画面。

#### 利用者

ログインできるものとして用意されたアカウントを持つ人。
