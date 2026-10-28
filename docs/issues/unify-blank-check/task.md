# Tasks

- [x] 1. 空白判定を一つにする
  - 1.1 AnnouncementVisibility の空白判定を共有し、値が無いか空白文字だけなら空白とする
  - 1.2 AnnouncementRegistrar の空白判定をその関数に置き換える
  - 1.3 既存のテスト一式を実行する

## Assumptions

- 共有する関数は `AnnouncementVisibility::isBlank` とし、新しいファイルは置かない。
- 値が `null` のときは空白だけである。登録は `null` を渡さない。
- 空白文字の判定は今の正規表現のままである。
