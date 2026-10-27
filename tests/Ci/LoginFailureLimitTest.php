<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(LazilyRefreshDatabase::class);

test('NFR-DSH-NEW-1 REQ-DSH-NEW-7 直近5回の不一致は正しいパスワードを拒否し、失敗のない別メールはログインできる', function () {
    $blocked = User::factory()->create();
    $open = User::factory()->create();

    Cache::put(
        'login-failures:'.hash('sha256', $blocked->email),
        array_fill(0, 5, now()->getTimestamp()),
        60,
    );

    $response = $this->post('/login', [
        'email' => $blocked->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors([
        'email' => 'メールアドレスまたはパスワードが正しくありません。',
    ]);
    $this->assertGuest();

    $this->post('/login', [
        'email' => $open->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($open);
});
