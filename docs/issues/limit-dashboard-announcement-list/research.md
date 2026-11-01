# Research

## Questions

- RQ1: ダッシュボードの一覧はどこで組まれるか
- RQ2: 公開対象の全件と詳細はどう分かれているか
- RQ3: 一覧を出す入口はどこか
- RQ4: 件数の切り位置はステアリングと要件のどこに収まるか

## Findings

### RQ1

- `DashboardController::index` は `AnnouncementCatalog::visible(now())` の成功値を、件数を切らずに `dashboard.index` へ渡す。(source: app/Http/Controllers/DashboardController.php:15)
- `dashboard.index` は渡された順にタイトルと要約を描き、0件のときは「お知らせはありません。」を出す。(source: resources/views/dashboard/index.blade.php:37)

### RQ2

- `visible` は保存済みのお知らせをすべて読み、PHP で公開判定してから `compare` で並べ、件数では切らない。(source: app/Announcements/AnnouncementCatalog.php:19)
- 詳細は `findVisible` が id の一件を公開判定する。(source: app/Http/Controllers/AnnouncementController.php:14)
- カタログのテストは `visible` の結果を公開対象の全件として読む。(source: tests/Feature/AnnouncementCatalogTest.php:38)

### RQ3

- `/dashboard` は `auth` の中で `DashboardController::index` だけが受ける。(source: routes/web.php:21)
- ログイン成功は名前付きルート `dashboard` へリダイレクトする。(source: app/Http/Controllers/LoginController.php:32)
- ウェルカムのダッシュボードへのリンクは同じ名前付きルートである。(source: resources/views/welcome.blade.php:26)
- 認証済みの利用者がログインを開いたときの行き先は、ダッシュボードへのリダイレクトとして REQ-DSH-8 にある。(source: REQ-DSH-8)

### RQ4

- `App\Announcements` は HTTP に依存しない。(source: docs/knowledge/steering/structure.md:61)
- `DashboardController` と `dashboard.index` は、すでにお知らせの閲覧へ写像されている。(source: docs/knowledge/steering/structure.md:73)
- この提案はステアリングを変えない。(source: docs/issues/limit-dashboard-announcement-list/proposal.md:18)
- 要件差分が変えるのは閲覧の spec と quality であり、interfaces と data は変えない。(source: docs/issues/limit-dashboard-announcement-list/requirements.md:51)

## Options

### D1: 一覧を10件で切る位置
- Drivers: 公開判定と並びのあとで切る。詳細の一件取得は残す。`visible` の全件はカタログのテストが使う。画面へ届く入口は `DashboardController::index` に集まる。
- Related: RQ1, RQ2, RQ3, RQ4

| Option | Pros | Cons |
|---|---|---|
| `DashboardController` が先頭10件を渡す | 公開判定と並びのあと、すべての入口で同じ10件になる | 件数の定数がコントローラに残る |
| `visible` が先頭10件を返す | 切り位置が並びの隣にある | 全件を読むカタログの契約が変わる |
| ビューが先頭10件だけ描く | 空の文言はそのまま使える | ビューへ渡る集合は全件のまま残る |
| 取得クエリを10件で打ち切る | 読む行数は減る | 公開判定より前だと下書きが枠を使う |

## Conclusions

- D1: `DashboardController` が先頭10件を渡す -> No ADR (お知らせの閲覧のダッシュボード一覧だけで、切り位置は後から移せる)

# Delta

none
