# Design

## Overview

公開期間の判定と保存は `AnnouncementRegistrar` に足す（D1）。失敗は今と同じく文字列のリストで返し、コントローラが文言に変える（D2）。開始と終了は文字の入力で、保存する列は既存の `starts_at` と `ends_at` である。新しい決定、列の追加、成功画面への期間表示は無い。

## Requirements Traceability

| ID | Components | Test Level |
|---|---|---|
| REQ-ANR-4 | AnnouncementRegistrationController | Integration |
| REQ-ANR-13 | AnnouncementRegistrationController | Integration |
| REQ-ANR-14 | AnnouncementRegistrar | Integration |
| REQ-ANR-15 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-1 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-2 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-3 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-4 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-5 | AnnouncementRegistrationController | Integration |
| REQ-ANR-NEW-6 | AnnouncementRegistrar | Integration |
| REQ-ANR-NEW-7 | AnnouncementRegistrationController | Integration |

## Components and Interfaces

### AnnouncementRegistrar
- Location: `app/Announcements/AnnouncementRegistrar.php`
- Responsibility: 題名、要約、本文、開始、終了を受け、下書きを一件追加するか、失敗の文字列リストを返す。空白の判定は `AnnouncementVisibility::isBlank` を使う。
- Depends on: `AnnouncementVisibility`、`Announcement`、`Result`
- Provides: `register(title, summary, body, startsAt, endsAt)`。開始と終了の既定は空文字で、空文字は期間が両方空である。

開始または終了は、空白だけなら空である。それ以外は、先頭から末尾まで `YYYY-MM-DD H` であるときだけ時までの日時である。`YYYY`、`MM`、`DD` は桁が固定の十進で、実在する年月日である。`H` は一桁または二桁の十進で、整数として 0 以上 23 以下である。この形に合わない値、たとえば分や秒やタイムゾーンの指定、`24`、存在しない日は、時までの日時ではない。合った値は、その年月日と時の UTC で、分と秒は 0 である。

失敗リストに入れるコードは、該当するものだけを次の順で並べる。`title`、`summary`、`body`、`starts_at`、`ends_at`、`starts_at_invalid`、`ends_at_invalid`、`window`。題名、要約、本文は空白だけのとき `title`、`summary`、`body` を入れる。一方だけが空のとき、空の側を `starts_at` または `ends_at` にする。空でなく時までの日時でもない側を `starts_at_invalid` または `ends_at_invalid` にする。両方が時までの日時で、終了の瞬間が開始の瞬間より前のときだけ `window` を入れる。失敗が一つでもあるときは追加しない。失敗が無く、期間が両方空なら `starts_at` と `ends_at` は null の下書きを一件追加する。失敗が無く、両方とも時までの日時で終了が開始以降なら、その UTC の時を保存した下書きを一件追加する。状態は draft のままである。

### AnnouncementRegistrationController
- Location: `app/Http/Controllers/AnnouncementRegistrationController.php`
- Responsibility: 管理者の登録画面に開始と終了の空の文字入力を出し、送信された文字を登録へ渡す。失敗なら同じ画面へ戻し、入力した文字と文言を残す。成功の画面には期間を出さない。
- Depends on: `AnnouncementRegistrar`
- Provides: `create`、`store`、`created`
- View: `resources/views/announcements/register.blade.php`

入力の名前は `starts_at` と `ends_at` で、種類は文字である。文字列でない送信値は空文字にする。失敗時の `withInput` は題名、要約、本文、開始、終了の送信された文字を残す。コードと文言の対応は次のとおりである。

| Code | Message |
|---|---|
| `title` | 題名がありません |
| `summary` | 要約がありません |
| `body` | 本文がありません |
| `starts_at` | 開始がありません |
| `ends_at` | 終了がありません |
| `starts_at_invalid` | 開始が日時ではありません |
| `ends_at_invalid` | 終了が日時ではありません |
| `window` | 終了が開始より前です |

`starts_at` と `starts_at_invalid` の文言は開始の入力に出す。終了も同様である。`window` の文言は期間に一つ出す。成功時にセッションへ載せるのは保存した題名だけである。

## Data Model

none

## Error Handling

| Case | Behavior |
|---|---|
| 開始と終了が両方空、または空白だけ | 下書きの開始と終了は null。期間の不足、不正、逆転は出さない |
| 一方だけが空 | 追加しない。空の側に「開始がありません」または「終了がありません」を出す |
| 空でなく `YYYY-MM-DD H` の時までの日時でもない | 追加しない。その側に「開始が日時ではありません」または「終了が日時ではありません」を出す |
| 両方が時までの日時で、終了が開始より前 | 追加しない。「終了が開始より前です」を一つ出す。開始や終了の不足は出さない |
| 題名、要約、本文の空白と、期間の失敗が同時 | 追加しない。両方の文言を出し、送った文字を残す |
| 0 時または 23 時 | その時の UTC、分 0 秒 0 として保存する |
| 時が 24 | 日時ではない。翌日の 0 時にはしない |

## Testing Strategy

| Test Level | Runs on | Notes |
|---|---|---|
| Integration | Local | Sail 上の Pest で、登録画面の入力と文言、下書きの有無、保存された UTC の時を見る。対象は登録の Feature と、データベースを使う `AnnouncementRegistrar` のテスト |

## Migration

none

# Delta

ステアリングの変更は無い。
