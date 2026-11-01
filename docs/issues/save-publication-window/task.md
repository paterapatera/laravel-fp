# Tasks

- [x] 1. 下書きに公開期間を保存する (P)
  - 1.1 空、時までの日時、片方だけ、逆転、時でない値を登録で判定する
    - Requirements: REQ-ANR-NEW-2, REQ-ANR-NEW-4, REQ-ANR-NEW-6
  - 1.2 両方空なら期間を空にし、終了が開始以降の時は UTC の下書きを一件追加する
    - Requirements: REQ-ANR-14, REQ-ANR-15, REQ-ANR-NEW-1
  - 1.3 登録の保存と拒否を Integration テストで確認する
    - Requirements: REQ-ANR-14, REQ-ANR-15, REQ-ANR-NEW-1, REQ-ANR-NEW-2, REQ-ANR-NEW-4, REQ-ANR-NEW-6
- [x] 2. 登録画面で期間の入力と不正を示す
  - Depends on: 1
  - 2.1 登録画面に空の開始と終了の文字入力を出す
    - Requirements: REQ-ANR-4
  - 2.2 失敗のコードを文言にし、入力した文字を残す
    - Requirements: REQ-ANR-13, REQ-ANR-NEW-3, REQ-ANR-NEW-5, REQ-ANR-NEW-7
  - 2.3 登録画面の入力、文言、残る文字を Integration テストで確認する
    - Requirements: REQ-ANR-4, REQ-ANR-13, REQ-ANR-NEW-3, REQ-ANR-NEW-5, REQ-ANR-NEW-7
