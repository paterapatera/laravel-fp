# Research

## Questions

- RQ1: 管理者はどこに記録されているか
- RQ2: お知らせの追加と変更拒否は今どうなっているか
- RQ3: 未ログインの保護画面はどこへ戻るか

## Findings

### RQ1

- 利用者の保存項目は名前、メール、パスワードで、管理者フラグは無い。 (source: database/migrations/0001_01_01_000000_create_users_table.php:14)
- `User` の fillable は名前、メール、パスワードである。 (source: app/Models/User.php:13)
- 要件は各利用者に必須の `admin` を持たせ、付いていない利用者を管理者ではないとする。 (source: DATA-ANR-NEW-1)
- 登録を求めても、その利用者の管理者フラグは変わらない。 (source: REQ-ANR-NEW-19)
- 管理者フラグの付与と表示は、お知らせの登録の範囲外である。 (source: docs/sdd/issues/announcement-register/requirements.md:172)

### RQ2

- お知らせの HTTP は詳細の GET だけで、見つからない詳細は 404 である。 (source: app/Http/Controllers/AnnouncementController.php:17)
- ルート名 `announcements.store`、`announcements.update`、`announcements.destroy`、`announcements.edit` は無い。 (source: tests/Feature/AnnouncementChangeTest.php:17)
- お知らせは題名、要約、本文、状態、開始、終了を fillable にする。 (source: app/Models/Announcement.php:11)
- お知らせは `updated_at` を持たない。 (source: app/Models/Announcement.php:17)
- 状態 `draft` は下書きである。 (source: app/Enums/AnnouncementStatus.php:7)
- ダッシュボードは公開対象の題名と要約だけを出し、登録へのリンクは無い。 (source: resources/views/dashboard/index.blade.php:29)
- 成功と失敗は `Result` でコントローラへ戻り、HTTP が応答に変える。 (source: docs/sdd/steering/architecture.md:10)

### RQ3

- ダッシュボードとお知らせ詳細は `auth` の内側にあり、未認証は名前付きルート `login` へ戻る。 (source: routes/web.php:19)
- 未ログインで `/dashboard` を開くとログインへ戻る。 (source: tests/Feature/DashboardAuthenticationTest.php:35)
- ウェルカムとログインは、文言「登録」とパス `/register` を出さない。 (source: tests/Feature/DashboardAuthenticationTest.php:250)

## Options

### D1: 管理者の記録
- Drivers: 登録の入力では権限が変わらない。追加パッケージを置かない
- Related: RQ1

| Option | Pros | Cons |
|---|---|---|
| 利用者に管理者フラグが付く | 利用者の記録に残り、登録入力では変わらない | 利用者テーブルを変える |
| 設定にあるメールだけが管理者 | テーブルを変えない | メールの変更で管理者を失う |
| 役割パッケージの役割で管理者になる | 役割を増やせる | 依存パッケージと付与の仕組みが増える |

### D2: 成功した結果の再送
- Drivers: 成功の再送では下書きが増えない。別の登録は一件増える
- Related: RQ2

| Option | Pros | Cons |
|---|---|---|
| POST のあと GET へ移す | 結果の再表示では保存しない | 成功画面がもう一つ要る |
| 同じ題名の再 POST を拒否する | POST を繰り返しても増えない | 同じ題名の別登録まで拒む |

## Conclusions

- D1: 利用者に管理者フラグが付く -> ADR-NEW-1
- D2: POST のあと GET へ移す -> No ADR (登録の成功画面だけが使い、後から戻せる)

# Delta

## ADDED

### ADR-NEW-1: 利用者に管理者フラグを持たせる
- description: 管理者は利用者の `admin` が付いていることであり、登録の入力では変わらない。
- tags: [accounts]
- slug: user-admin-flag

#### Context and Problem Statement

お知らせを登録できる利用者と、できない利用者を区別する。区別は利用者の記録に残り、登録画面から付与や解除ができない。

#### Decision Drivers

- 登録の入力では権限が変わらない
- 追加パッケージを置かない

#### Considered Options

- 利用者に管理者フラグが付く
- 設定にあるメールだけが管理者
- 役割パッケージの役割で管理者になる

#### Decision Outcome

Chosen option: 利用者に管理者フラグが付く, because フラグは利用者の記録に残り、fillable から外せば登録の入力では変わらない。

##### Consequences

- Good, because 登録画面から管理者を付与したり外したりできない。
- Bad, because 既存の利用者はフラグが付くまで管理者ではない。

#### Pros and Cons of the Options

##### 利用者に管理者フラグが付く

- Good, because 利用者ごとに一つの真偽で判定できる。
- Bad, because 利用者テーブルを変える。

##### 設定にあるメールだけが管理者

- Good, because テーブルを変えない。
- Bad, because メールの変更で管理者を失う。

##### 役割パッケージの役割で管理者になる

- Good, because 役割を後から増やせる。
- Bad, because 依存パッケージと付与の仕組みが増える。

#### Related

- [お知らせの登録](/specs/announcements/register/)
- [ダッシュボードへのアクセス](/specs/dashboard/access/)
