---
type: "Specification"
title: "お知らせの登録"
description: "管理者がお知らせの下書きを一件追加する。"
tags: [announcements]
---
# お知らせの登録

## Purpose
管理者がお知らせを下書きとして追加できるようにする。

## Scope
登録の入口、下書きの追加、空白だけの入力の拒否、成功した結果の再送。保存済みのお知らせの編集と削除、管理者権限の付与、管理者フラグの表示、公開期間の保存は範囲外である。一覧と詳細は [お知らせの閲覧](/specs/announcements/read/) に属する。お知らせの保存項目は [お知らせの記録](/specs/announcements/read/data.md) に属する。

## Definitions
登録画面は、新しいお知らせの題名、要約、本文を入力する画面である。空白だけとは、文字が無いか、空白文字だけであることをいう。題名、要約、本文の空白だけは空と同じである。

## Identifier
Code: ANR

## Requirements

### REQ-ANR-1: Show the registration link
管理者がダッシュボードを開いたとき、システムは登録画面へのリンクを示さなければならない。

#### Scenario: Admin opens the dashboard

- **前提** 管理者
- **もし** ダッシュボードを開く
- **ならば** 登録画面へのリンクが出る

### REQ-ANR-2: Hide the link from other users
管理者でないログイン済みの利用者がダッシュボードを開いたとき、システムは登録画面へのリンクを出さないままにしなければならない。

#### Scenario: Non-admin opens the dashboard

- **前提** 管理者でないログイン済みの利用者
- **もし** ダッシュボードを開く
- **ならば** 登録画面へのリンクは出ない

### REQ-ANR-3: Hide the link outside the dashboard
ダッシュボード以外を示している間、システムは登録画面へのリンクを出さないままにしなければならない。

#### Scenario: Admin opens a detail

- **前提** 管理者と、公開対象のお知らせ
- **もし** その詳細を開く
- **ならば** 登録画面へのリンクは出ない

#### Scenario: Admin opens the welcome page

- **前提** 管理者
- **もし** ウェルカムを開く
- **ならば** 登録画面へのリンクは出ない

#### Scenario: Registration succeeds

- **前提** 管理者と、空白以外の題名、要約、本文
- **もし** 登録に成功する
- **ならば** その結果に登録画面へのリンクは出ない

### REQ-ANR-4: Open an empty registration form
管理者が登録画面を開いたとき、システムは空の登録入力を示さなければならない。

#### Scenario: Open from the dashboard link

- **前提** 管理者と、保存済みのお知らせ
- **もし** ダッシュボードの登録画面へのリンクを開く
- **ならば** 題名、要約、本文の入力が空である
- **かつ** 保存済みの題名は出ない

#### Scenario: Open the registration URL

- **前提** 管理者と、保存済みのお知らせ
- **もし** 登録画面の URL を開く
- **ならば** 題名、要約、本文の入力が空である
- **かつ** 保存済みの題名は出ない

### REQ-ANR-5: Send guests who open registration to login
未ログインの訪問者が登録画面を開いたとき、システムはログインを示さなければならない。

#### Scenario: Guest opens the registration URL

- **前提** ログインしていない訪問者と、保存済みのお知らせ
- **もし** 登録画面の URL を開く
- **ならば** ログインが出る
- **かつ** その題名、要約、本文は出ない

### REQ-ANR-6: Send guests who submit registration to login
未ログインの訪問者が登録を求めた場合、システムはログインを示さなければならない。

#### Scenario: Guest submits registration

- **前提** ログインしていない訪問者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** ログインが出る
- **かつ** その題名は出ない

### REQ-ANR-7: Hide registration from a non-admin
管理者でないログイン済みの利用者が登録画面を開いたとき、システムは本文の無い見つからない結果を示さなければならない。

#### Scenario: Non-admin opens the registration URL

- **前提** 管理者でないログイン済みの利用者
- **もし** 登録画面の URL を開く
- **ならば** 見つからない結果が出る
- **かつ** 登録入力は出ない
- **かつ** 本文は出ない

### REQ-ANR-8: Hide a non-admin submission
管理者でないログイン済みの利用者が登録を求めた場合、システムは本文の無い見つからない結果を示さなければならない。

#### Scenario: Non-admin submits registration

- **前提** 管理者でないログイン済みの利用者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** 見つからない結果が出る
- **かつ** その題名は出ない
- **かつ** 本文は出ない

### REQ-ANR-9: Opening registration adds nothing for others
管理者以外が登録画面を開いた場合、システムはお知らせを追加しないままにしなければならない。

#### Scenario: Guest opens registration

- **前提** ログインしていない訪問者と、お知らせが一件ある
- **もし** 登録画面の URL を開く
- **ならば** お知らせは一件のままである

#### Scenario: Non-admin opens registration

- **前提** 管理者でないログイン済みの利用者と、お知らせが一件ある
- **もし** 登録画面の URL を開く
- **ならば** お知らせは一件のままである

### REQ-ANR-10: Submission adds nothing for others
管理者以外が登録を求めた場合、システムはお知らせを追加しないままにしなければならない。

#### Scenario: Guest submits registration

- **前提** ログインしていない訪問者と、お知らせが一件ある
- **もし** 空白以外の題名、要約、本文で登録を求める
- **ならば** お知らせは一件のままである

#### Scenario: Non-admin submits registration

- **前提** 管理者でないログイン済みの利用者と、お知らせが一件ある
- **もし** 空白以外の題名、要約、本文で登録を求める
- **ならば** お知らせは一件のままである

### REQ-ANR-11: Reject blank text
題名、要約、本文のいずれかが空白だけの登録を管理者が求めた場合、システムはお知らせを追加しないままにしなければならない。

#### Scenario: Blank title

- **前提** 管理者と、お知らせが一件も無い
- **もし** 題名が空で、要約と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### Scenario: Blank summary

- **前提** 管理者と、お知らせが一件も無い
- **もし** 要約が空で、題名と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### Scenario: Blank body

- **前提** 管理者と、お知らせが一件も無い
- **もし** 本文が空で、題名と要約に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### Scenario: Whitespace title

- **前提** 管理者と、お知らせが一件も無い
- **もし** 題名が空白だけで、要約と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### Scenario: Whitespace summary

- **前提** 管理者と、お知らせが一件も無い
- **もし** 要約が空白だけで、題名と本文に文字がある登録を求める
- **ならば** お知らせは一件も無い

#### Scenario: Whitespace body

- **前提** 管理者と、お知らせが一件も無い
- **もし** 本文が空白だけで、題名と要約に文字がある登録を求める
- **ならば** お知らせは一件も無い

### REQ-ANR-12: Indicate blank fields
題名、要約、本文のいずれかが空白だけの登録を管理者が求めた場合、システムは空白だけの項目を示さなければならない。

#### Scenario: Missing summary is indicated

- **前提** 管理者と、文字のある題名と本文、空の要約
- **もし** 登録を求める
- **ならば** 要約の不足が示される
- **かつ** 題名の不足は示されない

#### Scenario: Missing title and body are indicated

- **前提** 管理者と、空の題名、文字のある要約、空の本文
- **もし** 登録を求める
- **ならば** 題名の不足と本文の不足が示される

### REQ-ANR-13: Keep entered characters
不足を示している間、システムは入力済みの文字をそのまま残さなければならない。

#### Scenario: Markup stays as characters

- **前提** 管理者と、題名 `<b>注意</b>`、空の要約、文字のある本文
- **もし** 登録を求める
- **ならば** `<b>注意</b>` の文字が残る
- **かつ** 本文の文字が残る

### REQ-ANR-14: Add one draft
管理者が空白以外の題名、要約、本文で登録したとき、システムは状態が draft のお知らせを一件追加しなければならない。

#### Scenario: First draft

- **前提** 管理者と、お知らせが一件も無く、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** 状態が draft のお知らせが一件増える
- **かつ** ダッシュボードの一覧にその題名は出ない

#### Scenario: Retry after a blank summary

- **前提** 管理者と、要約が空のため追加されなかった題名と本文
- **もし** 要約を補って登録を求める
- **ならば** 状態が draft のお知らせが一件増える

#### Scenario: Second registration

- **前提** 管理者と、登録済みの下書きが一件ある
- **もし** 別の空白以外の題名、要約、本文で登録を求める
- **ならば** 状態が draft のお知らせがもう一件増える

### REQ-ANR-15: Leave the window empty
登録が成功したとき、システムは開始と終了が空のお知らせを残さなければならない。

#### Scenario: No window submitted

- **前提** 管理者と、空白以外の題名、要約、本文
- **もし** 開始と終了を付けずに登録を求める
- **ならば** そのお知らせの開始と終了は空である

#### Scenario: Submitted window is dropped

- **前提** 管理者と、空白以外の題名、要約、本文と、終了が開始より前の期間
- **もし** その期間を付けて登録を求める
- **ならば** そのお知らせの開始と終了は空である

### REQ-ANR-16: Show the title as entered
登録が成功したとき、システムは保存した題名の文字をそのまま示さなければならない。

#### Scenario: Title characters stay literal

- **前提** 管理者と、題名 `<b>注意</b>`、空白以外の要約と本文
- **もし** 登録を求める
- **ならば** `<b>注意</b>` の文字が出る

### REQ-ANR-17: Hide summary and body after success
登録が成功したとき、システムは要約と本文を出さないままにしなければならない。

#### Scenario: Summary and body stay off the result

- **前提** 管理者と、要約にも本文にも題名に無い文字がある
- **もし** 登録を求める
- **ならば** その要約は出ない
- **かつ** その本文は出ない

### REQ-ANR-18: Replaying success adds nothing
登録の成功を再送した場合、システムはその題名の下書きを一件のままにしなければならない。

#### Scenario: Replay the success result

- **前提** 管理者と、登録の成功で増えた題名の下書きが一件
- **もし** その成功の結果を再送する
- **ならば** その題名の下書きは一件のままである

### REQ-ANR-19: Keep the admin flag
ログイン済みの利用者が登録を求めた場合、システムはその利用者の管理者フラグを変えないままにしなければならない。

#### Scenario: Registration keeps an admin flag

- **前提** 管理者フラグが付いた利用者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** その利用者の管理者フラグは付いたままである

#### Scenario: Registration keeps a non-admin flag

- **前提** 管理者フラグが付いていないログイン済みの利用者と、空白以外の題名、要約、本文
- **もし** 登録を求める
- **ならば** その利用者の管理者フラグは付いていないままである

#### Scenario: Submitted flag does not grant admin

- **前提** 管理者フラグが付いていないログイン済みの利用者
- **もし** 管理者フラグを付ける入力を含めて登録を求める
- **ならば** その利用者の管理者フラグは付いていないままである

#### Scenario: Submitted flag does not remove admin

- **前提** 管理者フラグが付いた利用者と、空白以外の題名、要約、本文
- **もし** 管理者フラグを外す入力を含めて登録を求める
- **ならば** その利用者の管理者フラグは付いたままである

## Constraints and Assumptions

## Related
- [お知らせの閲覧](/specs/announcements/read/)
- [ダッシュボードへのアクセス](/specs/dashboard/access/)
