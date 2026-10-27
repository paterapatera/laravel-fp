<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

const AUTH_REJECTION = 'メールアドレスまたはパスワードが正しくありません。';

/**
 * @return list<string>
 */
function accountMissingPhrases(): array
{
    return [
        '存在しません',
        '見つかりません',
        '未登録',
        '登録されていません',
        'アカウントがありません',
        'These credentials do not match',
        'credentials do not match',
        'user not found',
        'No account',
    ];
}

function loginFailureCacheKey(string $email): string
{
    return 'login-failures:'.hash('sha256', $email);
}

test('REQ-DSH-NEW-1 未認証の GET /dashboard はログインに着地し、お知らせの題名・要約・本文を出さない', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));

    $this->followingRedirects()
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('ログイン')
        ->assertDontSee('announcement-title')
        ->assertDontSee('announcement-summary')
        ->assertDontSee('announcement-body');
});

test('REQ-DSH-NEW-5 一致するログインはダッシュボードへ着地する', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('REQ-DSH-NEW-5 未認証で /dashboard を訪れたあとのログインは intended のダッシュボードへ着地する', function () {
    $user = User::factory()->create();

    $this->get('/dashboard')->assertRedirect(route('login'));

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('REQ-DSH-NEW-5 不一致のあとの一致するログインはダッシュボードへ着地する', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect(route('login'));

    $this->assertGuest();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('REQ-DSH-NEW-5 60秒より古い失敗時刻が5件あっても正しいログインはダッシュボードへ着地する', function () {
    $user = User::factory()->create();
    $expired = now()->subSeconds(61)->getTimestamp();

    Cache::put(loginFailureCacheKey($user->email), array_fill(0, 5, $expired), 60);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('REQ-DSH-NEW-6 未知のメールアドレスと誤ったパスワードは同じ拒否文を出し、認証しない', function () {
    $user = User::factory()->create();

    $unknown = $this->post('/login', [
        'email' => 'missing-user@example.com',
        'password' => 'password',
    ]);

    $unknown->assertRedirect(route('login'));
    $unknown->assertSessionHasErrors(['email' => AUTH_REJECTION]);
    expect(session('errors')->all())->toBe([AUTH_REJECTION]);
    $this->assertGuest();

    $unknownPage = $this->followingRedirects()->post('/login', [
        'email' => 'missing-user@example.com',
        'password' => 'password',
    ]);
    $unknownPage->assertSee(AUTH_REJECTION);

    foreach (accountMissingPhrases() as $phrase) {
        $unknownPage->assertDontSee($phrase);
    }

    $wrong = $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $wrong->assertRedirect(route('login'));
    $wrong->assertSessionHasErrors(['email' => AUTH_REJECTION]);
    expect(session('errors')->all())->toBe([AUTH_REJECTION]);
    $this->assertGuest();

    $wrongPage = $this->followingRedirects()->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    $wrongPage->assertSee(AUTH_REJECTION);

    foreach (accountMissingPhrases() as $phrase) {
        $wrongPage->assertDontSee($phrase);
    }
});

test('REQ-DSH-NEW-8 認証済みの GET /login はダッシュボードへリダイレクトする', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/login')
        ->assertRedirect(route('dashboard'));
});

test('REQ-DSH-NEW-9 ダッシュボード、ウェルカム、未認証のログアウトは未認証のウェルカムへ戻り、誰も認証しない', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect(route('welcome'));

    $this->assertGuest();

    $this->get('/')
        ->assertOk()
        ->assertSee('ログイン')
        ->assertDontSee('ログアウト');

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('ログアウト');

    $this->post('/logout')->assertRedirect(route('welcome'));

    $this->assertGuest();

    $this->followingRedirects()
        ->post('/logout')
        ->assertOk()
        ->assertSee('ログイン')
        ->assertDontSee('ログアウト');

    $this->assertGuest();

    $this->post('/logout')->assertRedirect(route('welcome'));

    $this->assertGuest();
    expect(auth()->id())->toBeNull();
});

test('REQ-DSH-NEW-10 認証済みのウェルカムとダッシュボードはログアウトを表示する', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('ログアウト')
        ->assertSee('action="'.route('logout').'"', false);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('ログアウト')
        ->assertSee('action="'.route('logout').'"', false);
});

test('REQ-DSH-NEW-2 未認証のウェルカムはログインへのリンクを持つ', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('ログイン');
});

test('REQ-DSH-NEW-4 認証済みのウェルカムはダッシュボードへのリンクを持つ', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee('href="'.route('dashboard').'"', false)
        ->assertSee('ダッシュボード');
});

test('REQ-DSH-NEW-3 ウェルカムは未認証でも認証済みでもお知らせの区画を出さない', function () {
    $user = User::factory()->create();

    $guest = $this->get('/')->assertOk();
    $authenticated = $this->actingAs($user)->get('/')->assertOk();

    foreach ([$guest, $authenticated] as $response) {
        $html = $response->getContent();

        expect($html)->not->toContain('announcement')
            ->and($html)->not->toContain('お知らせ')
            ->and($html)->not->toContain('id="announcements"')
            ->and($html)->not->toContain('data-announcements');

        $response->assertDontSee('announcement-title')
            ->assertDontSee('announcement-summary')
            ->assertDontSee('announcement-body');
    }
});

test('REQ-DSH-NEW-11 ウェルカムとログインに自己登録の操作がない', function () {
    foreach (['/', '/login'] as $path) {
        $response = $this->get($path)->assertOk();

        $response->assertDontSee('登録')
            ->assertDontSee('Register')
            ->assertDontSee('/register')
            ->assertDontSee('href="'.url('/register').'"', false);
    }
});

test('REQ-DSH-NEW-13 ウェルカムとログインにパスワード再設定の操作がない', function () {
    foreach (['/', '/login'] as $path) {
        $this->get($path)
            ->assertOk()
            ->assertDontSee('パスワードを忘れた')
            ->assertDontSee('パスワード再設定')
            ->assertDontSee('forgot-password')
            ->assertDontSee('reset-password')
            ->assertDontSee('/password/reset')
            ->assertDontSee('Forgot your password');
    }
});

test('REQ-DSH-NEW-12 POST /register 相当は利用者を作らず、そのメールではログインできない', function () {
    User::factory()->create();

    expect(User::query()->count())->toBe(1);

    $email = 'new-person@example.com';

    $this->post('/register', [
        'name' => 'New Person',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->post('/signup', [
        'name' => 'New Person',
        'email' => $email,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(User::query()->count())->toBe(1);

    $this->post('/login', [
        'email' => $email,
        'password' => 'password',
    ])->assertRedirect(route('login'));

    $this->assertGuest();
});

test('REQ-DSH-NEW-14 パスワード再設定の直接要求は保存済みパスワードを変えず、以前のパスワードでログインできる', function () {
    $user = User::factory()->create();
    $storedPassword = $user->password;

    $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'replacement-password',
        'password_confirmation' => 'replacement-password',
    ]);

    $this->post('/password/email', [
        'email' => $user->email,
    ]);

    $this->post('/password/reset', [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'replacement-password',
        'password_confirmation' => 'replacement-password',
    ]);

    expect($user->fresh()->password)->toBe($storedPassword);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('REQ-DSH-NEW-7 直近の不一致が5件あるメールは正しいパスワードでも拒否され、失敗のない別利用者はログインできる', function () {
    $blocked = User::factory()->create();
    $open = User::factory()->create();

    Cache::put(
        loginFailureCacheKey($blocked->email),
        array_fill(0, 5, now()->getTimestamp()),
        60,
    );

    $response = $this->post('/login', [
        'email' => $blocked->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email' => AUTH_REJECTION]);
    expect(session('errors')->all())->toBe([AUTH_REJECTION]);
    $this->assertGuest();

    $this->followingRedirects()
        ->post('/login', [
            'email' => $blocked->email,
            'password' => 'password',
        ])
        ->assertSee(AUTH_REJECTION)
        ->assertDontSee('ダッシュボード');

    $this->post('/login', [
        'email' => $open->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($open);
});
