# Research

## Questions

- RQ1: 認証のガードとログインルートはどうなっているか
- RQ2: 未ログインの保護画面はどこへ戻るか
- RQ3: 本文を平文のまま出す手段は何か
- RQ4: 直近1分の失敗回数はどの API で数えるか

## Findings

### RQ1

- 既定ガードは `web` で、ドライバは `session`、利用者は Eloquent の `User` である。(source: config/auth.php:40)
- Web ルートは `/` のウェルカムだけで、ログインのルートは無い。(source: routes/web.php:5)
- `User` は `Authenticatable` で、パスワードは `hashed` キャストである。(source: app/Models/User.php:15)
- モデルは `Result` に依存しない。(source: docs/sdd/steering/structure.md:60)

### RQ2

- ゲストを戻す既定は、名前付きルート `login` である。(source: vendor/laravel/framework/src/Illuminate/Foundation/Configuration/ApplicationBuilder.php:291)
- ログイン済みがゲスト向けルートを開くと、名前付きルート `dashboard` があればそこへ戻る。(source: vendor/laravel/framework/src/Illuminate/Auth/Middleware/RedirectIfAuthenticated.php:65)
- アプリケーションのミドルウェア設定は空である。(source: bootstrap/app.php:14)

### RQ3

- `e()` は `htmlspecialchars` で特殊文字をエンコードする。(source: vendor/laravel/framework/src/Illuminate/Support/helpers.php:124)

### RQ4

- `tooManyAttempts` は、カウンタが上限以上のとき真になる。(source: vendor/laravel/framework/src/Illuminate/Cache/RateLimiter.php:129)
- `increment` は最初の加算でカウンタ全体に一つの期限を付ける。(source: vendor/laravel/framework/src/Illuminate/Cache/RateLimiter.php:164)
- 開発のキャッシュは database、テストのキャッシュは array である。(source: .env.example:40)
- テストのキャッシュが array であることは `phpunit.xml` にもある。(source: phpunit.xml:25)
- アプリケーションのタイムゾーンは UTC である。(source: config/app.php:68)

## Options

### D1: ログインの実装方式
- Drivers: 登録と再設定のルートを最初から置かない
- Related: RQ1

| Option | Pros | Cons |
|---|---|---|
| 自前のセッション画面 | 既存の `web` ガードだけで足りる | ログイン画面と失敗上限をこのアプリが持つ |
| Fortify のログインだけ | 認証の定番パッケージを使える | 登録と再設定の機能を無効にし続ける必要がある |
| 公式スターターキット | 画面一式が最初から付く | 登録、再設定、別フロントが要件の外から入る |

### D2: 要約と本文の形式
- Drivers: 入力を HTML として解釈しない
- Related: RQ3

| Option | Pros | Cons |
|---|---|---|
| エスケープした平文 | タグが文字として見える | 装飾はできない |
| 許可タグだけの HTML | 最小限の装飾ができる | 許可の抜けがそのまま実行される |
| 入力そのままの HTML | 見たままを出せる | 保存した文が画面上で実行される |

### D3: 詳細の識別子と見つからない応答
- Drivers: 下書きと欠番を同じ結果にする
- Related: RQ2

| Option | Pros | Cons |
|---|---|---|
| 整数の主キーと HTTP 404 | 欠番も非公開も同じ応答になる | 番号の並びから件数を推測できる |
| UUID と HTTP 404 | 件数を番号から読めない | 識別子の型が増える |
| 整数の主キーと HTTP 403 | 存在するが拒否だと分かる | 下書きの存在が応答で分かる |

### D4: 公開データの投入
- Drivers: 利用者の画面から保存しない
- Related: RQ1

| Option | Pros | Cons |
|---|---|---|
| Factory と Seeder | テストとローカル確認が同じ形になる | 本番の追加はシーダの実行になる |
| 追記用の Artisan | 運用がコードを変えずに追加できる | 変更の入口がもう一つできる |
| 手順書だけの手投入 | アプリケーションの入口が増えない | テストデータを毎回手で作る |

### D5: 失敗回数の窓
- Drivers: 直近1分の件数を超えない
- Related: RQ4

| Option | Pros | Cons |
|---|---|---|
| 直近60秒の時刻リスト | 古い失敗から順に窓の外へ出る | カウンタを自前で持つ |
| RateLimiter の固定窓 | フレームワークのカウンタを使える | 窓の途中の失敗がまとめて消える |

## Conclusions

- D1: 自前のセッション画面 -> ADR-NEW-1
- D2: エスケープした平文 -> No ADR (お知らせの表示だけで、保存された平文は後から別の描画に変えられる)
- D3: 整数の主キーと HTTP 404 -> No ADR (お知らせ詳細だけの URL で、公開前なので後から変えられる)
- D4: Factory と Seeder -> No ADR (開発とテストの投入だけで、運用コマンドは後から足せる)
- D5: 直近60秒の時刻リスト -> No ADR (測り方は NFR が既に決めており、キャッシュの中身は後から差し替えられる)

# Delta

## ADDED

### ADR-NEW-1: ログインは自前のセッション画面にする
- description: 用意された利用者のログインは、追加パッケージのないセッション画面で行う。
- tags: [dashboard]
- slug: login-session

#### Context and Problem Statement

ダッシュボードとお知らせは、用意された利用者の同じセッションで開く。登録とパスワード再設定の入口は持たない。

#### Decision Drivers

- 登録とパスワード再設定のルートを最初から置かない
- ダッシュボードとお知らせが既存の `web` ガードを使う
- 依存パッケージを増やさない

#### Considered Options

- 自前のセッション画面
- Fortify のログインだけ
- 公式スターターキット

#### Decision Outcome

Chosen option: 自前のセッション画面, because 登録と再設定の入口を置かず、両能力が既存の `web` ガードを使える。

##### Consequences

- Good, because 登録と再設定のルートが最初から無い。
- Bad, because ログイン画面と失敗上限をこのアプリケーションが持つ。

#### Pros and Cons of the Options

##### 自前のセッション画面

- Good, because 既存の `web` ガードだけで足りる。
- Bad, because ログイン画面と失敗上限をこのアプリケーションが持つ。

##### Fortify のログインだけ

- Good, because 認証の定番パッケージを使える。
- Bad, because 登録と再設定の機能を無効にし続ける必要がある。

##### 公式スターターキット

- Good, because 画面一式が最初から付く。
- Bad, because 登録、再設定、別フロントが要件の外から入る。

#### Related

- [ダッシュボードへのアクセス](/specs/dashboard/access/)
- [お知らせの閲覧](/specs/announcements/read/)
