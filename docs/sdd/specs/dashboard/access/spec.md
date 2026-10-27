---
type: "Specification"
title: "ダッシュボードへのアクセス"
description: "用意された利用者のログイン、ログアウト、ダッシュボードへの到達。"
tags: [dashboard]
---
# ダッシュボードへのアクセス

## Purpose
用意された利用者がログインし、ダッシュボードへ到達できるようにする。

## Scope
ウェルカムからの入口、ログイン、ログアウト、未認証のときの保護画面の拒否。お知らせの中身は [お知らせの閲覧](/specs/announcements/read/) に属する。

## Definitions
ダッシュボードは、パス `/dashboard` の画面である。ログインは、用意された利用者のメールアドレスとパスワードを受け付ける画面である。保護画面は、ダッシュボードとお知らせの詳細である。未認証の拒否は、セッションを開始せず、アカウントの有無が判別できない表示である。ログイン失敗は、入力されたメールアドレスとパスワードが用意された利用者と一致しない試行である。上限は NFR-DSH-1 である。

## Identifier
Code: DSH

## Requirements

### REQ-DSH-1: Send guests to login
未ログインの訪問者が保護画面を開いたとき、システムはお知らせの内容が無いログインを示さなければならない。

#### Scenario: Guest opens the dashboard
- **前提** ログインしていない訪問者
- **もし** `/dashboard` を開く
- **ならば** ログインが出る
- **かつ** お知らせのタイトル、要約、本文は出ない

#### Scenario: Guest opens a detail
- **前提** ログインしていない訪問者と、お知らせがある
- **もし** その詳細を開く
- **ならば** ログインが出る
- **かつ** その本文は出ない

#### Scenario: Guest opens a missing detail
- **前提** ログインしていない訪問者と、該当するお知らせが無い
- **もし** お知らせの詳細を開く
- **ならば** ログインが出る
- **かつ** 本文は出ない

### REQ-DSH-2: Link to login from the welcome page
未ログインの訪問者がウェルカムを開いたとき、システムはログインへのリンクを示さなければならない。

#### Scenario: Guest opens the welcome page
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** ログインへのリンクが出る

### REQ-DSH-3: Keep announcements off the welcome page
ウェルカムを開いたとき、システムはお知らせのタイトル、要約、本文を出さないままにしなければならない。

#### Scenario: Guest welcome
- **前提** ログインしていない訪問者と、お知らせがある
- **もし** ウェルカムを開く
- **ならば** そのタイトル、要約、本文は出ない

#### Scenario: Authenticated welcome
- **前提** ログイン済みの利用者と、お知らせがある
- **もし** ウェルカムを開く
- **ならば** そのタイトル、要約、本文は出ない

### REQ-DSH-4: Link to the dashboard from the welcome page
ログイン済みの利用者がウェルカムを開いたとき、システムはダッシュボードへのリンクを示さなければならない。

#### Scenario: Authenticated user opens the welcome page
- **前提** ログイン済みの利用者
- **もし** ウェルカムを開く
- **ならば** ダッシュボードへのリンクが出る

### REQ-DSH-5: Enter the requested screen after login
上限内で認証情報が一致するログインに成功したとき、システムは直前に求めた保護画面を、求めた画面が無ければダッシュボードを示さなければならない。

#### Scenario: Login directly
- **前提** 用意された利用者と、保護画面を求める前のログイン
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

#### Scenario: Login after a detail request
- **前提** 用意された利用者と、未ログインのまま求めた、本文を読めるお知らせの詳細
- **もし** 一致する認証情報でログインする
- **ならば** その詳細が出る

#### Scenario: Login after a dashboard request
- **前提** 用意された利用者と、未ログインのまま求めた `/dashboard`
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

#### Scenario: Retry after a mismatch
- **前提** 用意された利用者と、上限内の不一致による未認証の拒否
- **もし** 続けて一致する認証情報でログインする
- **ならば** ダッシュボードが出る

#### Scenario: Login after the limit expires
- **前提** 用意された利用者と、そのメールアドレスへの不一致が5回あるが、どれも直近1分より前である
- **もし** 一致する認証情報でログインする
- **ならば** ダッシュボードが出る

### REQ-DSH-6: Hide whether the account exists
認証情報が一致しない場合、システムは未認証の拒否を示さなければならない。

#### Scenario: Unknown email
- **前提** 用意されていないメールアドレス
- **もし** そのメールアドレスでログインする
- **ならば** 未認証の拒否が出る
- **かつ** アカウントが無いことは示されない

#### Scenario: Wrong password
- **前提** 用意された利用者と、一致しないパスワード
- **もし** その利用者のメールアドレスでログインする
- **ならば** 未認証の拒否が出る
- **かつ** 用意されていないメールアドレスのときと同じ拒否である

### REQ-DSH-7: Refuse logins past the limit
上限を超えたログインの場合、システムは認証情報が一致しても未認証の拒否を示さなければならない。

#### Scenario: Matching password within the minute
- **前提** 用意された利用者と、そのメールアドレスへの不一致が直近1分に5回ある
- **もし** 一致する認証情報でログインする
- **ならば** 未認証の拒否が出る
- **かつ** ダッシュボードは出ない

#### Scenario: Another email still logs in
- **前提** 二人の用意された利用者と、一方のメールアドレスへの不一致が直近1分に5回ある
- **もし** もう一方が一致する認証情報でログインする
- **ならば** その利用者のセッションが始まる

### REQ-DSH-8: Leave the login screen when already authenticated
ログイン済みの利用者がログインを開いたとき、システムはダッシュボードを示さなければならない。

#### Scenario: Authenticated user opens login
- **前提** ログイン済みの利用者
- **もし** ログインを開く
- **ならば** ダッシュボードが出る

### REQ-DSH-9: Return to the welcome page after logout
ログアウトしたとき、システムは未認証のウェルカムを示さなければならない。

#### Scenario: Logout from the dashboard
- **前提** ダッシュボードを見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る
- **かつ** お知らせの内容は出ない

#### Scenario: Logout from a detail
- **前提** お知らせ詳細を見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る

#### Scenario: Logout from the welcome page
- **前提** ウェルカムを見ているログイン済みの利用者
- **もし** ログアウトする
- **ならば** 未認証のウェルカムが出る

#### Scenario: Guest requests logout
- **前提** ログインしていない訪問者
- **もし** ログアウトを求める
- **ならば** 未認証のウェルカムが出る
- **かつ** セッションは始まらない

### REQ-DSH-10: Offer logout on authenticated screens
ログイン済みの利用者がウェルカム、ダッシュボード、またはお知らせ詳細を見ている間、システムはログアウトの操作を示さなければならない。

#### Scenario: Logout on the welcome page
- **前提** ログイン済みの利用者
- **もし** ウェルカムを開く
- **ならば** ログアウトの操作が出る

#### Scenario: Logout on the dashboard
- **前提** ログイン済みの利用者
- **もし** ダッシュボードを開く
- **ならば** ログアウトの操作が出る

#### Scenario: Logout on a detail
- **前提** ログイン済みの利用者と、本文を読めるお知らせがある
- **もし** その詳細を開く
- **ならば** ログアウトの操作が出る

### REQ-DSH-11: Offer no self-registration
システムは、自己登録の操作が無いログインとウェルカムを示さなければならない。

#### Scenario: Welcome has no registration
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** 自己登録の操作は無い

#### Scenario: Login has no registration
- **前提** ログインしていない訪問者
- **もし** ログインを開く
- **ならば** 自己登録の操作は無い

### REQ-DSH-12: Reject self-registration
自己登録を求められた場合、システムは新しい利用者を作らずに拒否しなければならない。

#### Scenario: Direct registration request
- **前提** 利用者のアカウントが一件ある
- **もし** 新しいメールアドレスの自己登録を求める
- **ならば** アカウントは一件のままである
- **かつ** その新しいメールアドレスではログインできない

### REQ-DSH-13: Offer no password reset
システムは、パスワード再設定の操作が無いログインとウェルカムを示さなければならない。

#### Scenario: Login has no reset
- **前提** ログインしていない訪問者
- **もし** ログインを開く
- **ならば** パスワード再設定の操作は無い

#### Scenario: Welcome has no reset
- **前提** ログインしていない訪問者
- **もし** ウェルカムを開く
- **ならば** パスワード再設定の操作は無い

### REQ-DSH-14: Reject password reset
パスワード再設定を求められた場合、システムは保存済みのパスワードを維持しなければならない。

#### Scenario: Direct reset request
- **前提** 用意された利用者
- **もし** そのパスワードの再設定を求める
- **ならば** これまでのパスワードでログインできる

## Constraints and Assumptions

## Related
- [お知らせの閲覧](/specs/announcements/read/)
