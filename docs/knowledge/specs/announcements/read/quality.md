---
type: "Quality Requirements"
title: "一覧の件数"
description: "ダッシュボードに出す公開対象の件数。"
tags: [announcements]
---
# お知らせの閲覧

## Purpose
ダッシュボードに出す公開対象の件数を限る。

## Quality Requirements

### NFR-ANN-1: Announcement list limit
- Characteristic: Functional suitability
- Measure: ダッシュボードに出ている公開対象の件数
- Target: 10件以下
- Condition: ログイン済みの利用者がダッシュボードを開いている
- Verification: Test

ログイン済みの利用者がダッシュボードを開いたとき、システムはダッシュボードに出す公開対象を10件以下にしなければならない。

## Related
