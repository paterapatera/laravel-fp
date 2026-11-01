# Design

## Overview

ダッシュボードに出す公開対象は、`AnnouncementCatalog::visible` が今の順で返した全件のうち、先頭10件である（D1）。切り出して渡すのは `DashboardController::index` だけであり、ログイン成功、ウェルカムのリンク、認証済み利用者のログイン、`/dashboard` の直接アクセスは、どれもこの `index` に届く。`visible` は公開判定と並びを終えてから返すので、下書きは10件の枠を使わない。件数が10未満なら、その全件を渡す。開始、作成時刻、タイトルが同じ公開対象が10件を超えても、並びの比較のあとで件数を10にする。`findVisible` と詳細画面は、この件数に影響されない。`dashboard.index` は渡された項目のタイトルと要約を描き、0件のときは今の「お知らせはありません。」を出す。画面に続き、全件数、残件数を示す箇所は置かない。

## Requirements Traceability

| ID | Components | Test Level |
|---|---|---|
| REQ-ANN-1 | DashboardController, dashboard.index | Integration |
| REQ-ANN-NEW-1 | DashboardController | Integration |
| REQ-ANN-NEW-2 | DashboardController | Integration |
| REQ-ANN-NEW-3 | dashboard.index | Integration |
| NFR-ANN-NEW-1 | DashboardController | Integration |

## Components and Interfaces

### DashboardController
- Location: app/Http/Controllers/DashboardController.php
- Responsibility: `visible` の成功値から先頭10件を `dashboard.index` へ渡す。10は設定にしない。
- Depends on: AnnouncementCatalog
- Provides: index()

### dashboard.index
- Location: resources/views/dashboard/index.blade.php
- Responsibility: 渡された公開対象のタイトルと要約を、渡された順に描く。0件のときは「お知らせはありません。」を出す。
- Depends on: DashboardController が渡す announcements
- Provides: ダッシュボードの一覧

### AnnouncementCatalog
- Location: app/Announcements/AnnouncementCatalog.php
- Responsibility: 公開対象の全件を今の順で返す。詳細の一件は `findVisible` が公開判定する。
- Depends on: AnnouncementVisibility
- Provides: visible(now), findVisible(id, now)

## Data Model

none

## Error Handling

none

## Testing Strategy

| Test Level | Runs on | Notes |
|---|---|---|
| Integration | Local | ダッシュボードの HTTP 応答で、先頭10件、11件目の不在、10件以下の全件、同点でも10件、下書きが枠を使わないこと、入口ごとの同じ先頭、省略の知らせが無いこと、0件の空の文言を確かめる |

## Migration

none

# Delta

none
