---
type: "Quality Requirements"
title: "ログイン失敗の上限"
description: "同一アカウントのログイン失敗回数。"
tags: [dashboard]
---
# ダッシュボードへのアクセス

## Purpose
同じメールアドレスへのログイン失敗を、短時間に繰り返せないようにする。

## Quality Requirements

### NFR-DSH-1: Login failure limit
- Characteristic: Security
- Measure: 入力されたメールアドレスごとの直近1分のログイン失敗回数
- Target: 5回以下
- Condition: 認証情報が一致しなかった試行を、入力されたメールアドレスごとに数える
- Verification: Test

システムは、同一メールアドレスのログイン失敗の上限を直近1分あたり5回にしなければならない。

## Related
