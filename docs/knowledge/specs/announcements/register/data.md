---
type: "Data Specification"
title: "利用者の管理者フラグ"
description: "お知らせを登録できる利用者を区別する保存項目。"
tags: [announcements]
---
# お知らせの登録

## Purpose
登録できる利用者を、利用者に付く管理者フラグで区別して保つ。

## Entities

### DATA-ANR-1: User admin flag
- Classification: Internal
- Retention: 利用者がある間

システムは各利用者の管理者フラグを永続化しなければならない。

| Element | Type | Required | Constraints | Description |
|---|---|---|---|---|
| admin | boolean | yes | 利用者ごとに一つ。付いていない利用者は管理者ではない | 管理者であるか |

## Related
