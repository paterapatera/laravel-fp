---
type: "Data Specification"
title: "お知らせの記録"
description: "利用者が読むお知らせの保存項目。"
tags: [announcements]
---
# お知らせの閲覧

## Purpose
公開判定と一覧と詳細に使うお知らせを保持する。

## Entities

### DATA-ANN-1: Announcement
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

## Related
