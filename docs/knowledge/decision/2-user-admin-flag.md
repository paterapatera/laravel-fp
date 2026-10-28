---
type: "Decision"
title: "利用者に管理者フラグを持たせる"
description: "管理者は利用者の `admin` が付いていることであり、登録の入力では変わらない。"
tags: [accounts]
---
# ADR-2: 利用者に管理者フラグを持たせる

## Status
Accepted
Date: 2026-10-28

## Context and Problem Statement
お知らせを登録できる利用者と、できない利用者を区別する。区別は利用者の記録に残り、登録画面から付与や解除ができない。

## Decision Drivers
- 登録の入力では権限が変わらない
- 追加パッケージを置かない

## Considered Options
- 利用者に管理者フラグが付く
- 設定にあるメールだけが管理者
- 役割パッケージの役割で管理者になる

## Decision Outcome
Chosen option: 利用者に管理者フラグが付く, because フラグは利用者の記録に残り、fillable から外せば登録の入力では変わらない。

### Consequences

- Good, because 登録画面から管理者を付与したり外したりできない。
- Bad, because 既存の利用者はフラグが付くまで管理者ではない。

## Pros and Cons of the Options
### 利用者に管理者フラグが付く

- Good, because 利用者ごとに一つの真偽で判定できる。
- Bad, because 利用者テーブルを変える。

### 設定にあるメールだけが管理者

- Good, because テーブルを変えない。
- Bad, because メールの変更で管理者を失う。

### 役割パッケージの役割で管理者になる

- Good, because 役割を後から増やせる。
- Bad, because 依存パッケージと付与の仕組みが増える。

## Related
- [お知らせの登録](/specs/announcements/register/)
- [ダッシュボードへのアクセス](/specs/dashboard/access/)

