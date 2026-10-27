---
type: "Decision"
title: "ログインは自前のセッション画面にする"
description: "用意された利用者のログインは、追加パッケージのないセッション画面で行う。"
tags: [dashboard]
---
# ADR-1: ログインは自前のセッション画面にする

## Status
Accepted
Date: 2026-10-28

## Context and Problem Statement
ダッシュボードとお知らせは、用意された利用者の同じセッションで開く。登録とパスワード再設定の入口は持たない。

## Decision Drivers
- 登録とパスワード再設定のルートを最初から置かない
- ダッシュボードとお知らせが既存の `web` ガードを使う
- 依存パッケージを増やさない

## Considered Options
- 自前のセッション画面
- Fortify のログインだけ
- 公式スターターキット

## Decision Outcome
Chosen option: 自前のセッション画面, because 登録と再設定の入口を置かず、両能力が既存の `web` ガードを使える。

### Consequences

- Good, because 登録と再設定のルートが最初から無い。
- Bad, because ログイン画面と失敗上限をこのアプリケーションが持つ。

## Pros and Cons of the Options
### 自前のセッション画面

- Good, because 既存の `web` ガードだけで足りる。
- Bad, because ログイン画面と失敗上限をこのアプリケーションが持つ。

### Fortify のログインだけ

- Good, because 認証の定番パッケージを使える。
- Bad, because 登録と再設定の機能を無効にし続ける必要がある。

### 公式スターターキット

- Good, because 画面一式が最初から付く。
- Bad, because 登録、再設定、別フロントが要件の外から入る。

## Related
- [ダッシュボードへのアクセス](/specs/dashboard/access/)
- [お知らせの閲覧](/specs/announcements/read/)

