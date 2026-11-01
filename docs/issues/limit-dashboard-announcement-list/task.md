# Tasks

- [ ] 1. ダッシュボードの公開対象を先頭10件に限る (P)
  - 1.1 `DashboardController::index` で `AnnouncementCatalog::visible` の成功値から先頭10件だけを `dashboard.index` へ渡す。10は設定にせず、`visible` の全件返却と `findVisible` は変えない
    - Requirements: REQ-ANN-NEW-1, REQ-ANN-NEW-2, NFR-ANN-NEW-1
  - 1.2 渡された公開対象のタイトルと要約を渡し順に描き、0件のときは「お知らせはありません。」のままにする
    - Requirements: REQ-ANN-1
  - 1.3 ダッシュボードに続きがあること、全件数、残件数を出さない
    - Requirements: REQ-ANN-NEW-3
  - 1.4 先頭10件、10件未満の全件、ちょうど10件、作成時刻とタイトルの同点でも10件、下書きが枠を使わないことを、ダッシュボードの HTTP 応答の統合テストで確かめる
    - Requirements: REQ-ANN-NEW-1, NFR-ANN-NEW-1
  - 1.5 11件目以降が出ないことと、`/dashboard`、ウェルカムのリンク、保護画面を求める前のログイン、不一致のあとの再試行、ログイン済み利用者がログインを開いたとき、管理者とそれ以外で同じ先頭になることを、統合テストで確かめる
    - Requirements: REQ-ANN-NEW-2, REQ-ANN-NEW-1
  - 1.6 省略の知らせが無いこと、一覧に含める公開対象のタイトルと要約、0件の空の文言を、統合テストで確かめる
    - Requirements: REQ-ANN-NEW-3, REQ-ANN-1
