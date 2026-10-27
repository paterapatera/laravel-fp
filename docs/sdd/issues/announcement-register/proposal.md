# 管理者がお知らせの下書きを登録できる

## Why

運用が画面から告知を残せるようにする。ログイン済みの利用者はお知らせを読むだけで、作成は拒否される。

## What Changes

- 管理者権限のある利用者は、お知らせを下書きとして一件追加できる。
- 管理者権限の無い利用者は、お知らせを追加できない。

## Impact

- Type: feat
- Capabilities: New /specs/announcements/register（既存ドメイン「お知らせ」）
- Steering: /steering/product.md, /steering/glossary.md
- Decisions: none
- Code: お知らせの下書きを登録する画面と保存

## Out of Scope

- 保存済みお知らせの編集
- お知らせの削除
- 管理者権限の付与
