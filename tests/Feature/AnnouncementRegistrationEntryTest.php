<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('REQ-ANN-10 REQ-ANR-NEW-1 REQ-ANR-NEW-2 ダッシュボードは管理者にだけ登録リンクを出し、編集と削除は出さない', function () {
    $admin = User::factory()->create(['admin' => true]);
    $plain = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('announcements.register.create').'"', false)
        ->assertSee('登録')
        ->assertDontSee('編集')
        ->assertDontSee('削除');

    $this->actingAs($plain)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('登録')
        ->assertDontSee(route('announcements.register.create'), false)
        ->assertDontSee('編集')
        ->assertDontSee('削除');
});

test('REQ-ANN-10 REQ-ANR-NEW-3 詳細とウェルカムに登録リンクは無く、詳細に編集と削除は無い', function () {
    $admin = User::factory()->create(['admin' => true]);
    $announcement = Announcement::factory()->create([
        'title' => '公開題名UNIQUE',
        'body' => '公開本文UNIQUE',
    ]);

    $this->actingAs($admin)
        ->get(route('announcements.show', $announcement))
        ->assertOk()
        ->assertSee('公開題名UNIQUE')
        ->assertDontSee(route('announcements.register.create'), false)
        ->assertDontSee('登録')
        ->assertDontSee('編集')
        ->assertDontSee('削除');

    $this->actingAs($admin)
        ->get(route('welcome'))
        ->assertOk()
        ->assertDontSee(route('announcements.register.create'), false)
        ->assertDontSee('登録');
});

test('REQ-ANR-NEW-4 管理者の登録画面は空で、保存済みの題名は出ない', function () {
    Announcement::factory()->create(['title' => '保存済み題名UNIQUE']);
    $admin = User::factory()->create(['admin' => true]);

    $this->actingAs($admin)
        ->get(route('announcements.register.create'))
        ->assertOk()
        ->assertSee('name="title"', false)
        ->assertSee('value=""', false)
        ->assertSee('name="summary"', false)
        ->assertSee('name="body"', false)
        ->assertDontSee('保存済み題名UNIQUE')
        ->assertDontSee('href="'.route('announcements.register.create').'"', false);

    expect(Announcement::query()->count())->toBe(1);
});

test('REQ-ANR-NEW-5 REQ-ANR-NEW-9 未ログインが登録画面を開くとログインへ移り、お知らせは増えない', function () {
    Announcement::factory()->create([
        'title' => '既存題名UNIQUE',
        'summary' => '既存要約UNIQUE',
        'body' => '既存本文UNIQUE',
    ]);

    $this->get(route('announcements.register.create'))
        ->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('既存題名UNIQUE')
        ->assertDontSee('既存要約UNIQUE')
        ->assertDontSee('既存本文UNIQUE')
        ->assertDontSee('name="title"', false);

    expect(Announcement::query()->count())->toBe(1);
});

test('REQ-ANR-NEW-7 REQ-ANR-NEW-9 管理者でないログイン済み利用者が登録画面を開くと本文の無い 404 で、お知らせは増えない', function () {
    Announcement::factory()->create(['title' => '既存の題名UNIQUE', 'body' => '既存の本文UNIQUE']);
    $plain = User::factory()->create();

    $this->actingAs($plain)
        ->get(route('announcements.register.create'))
        ->assertNotFound()
        ->assertContent('')
        ->assertDontSee('既存の題名UNIQUE')
        ->assertDontSee('既存の本文UNIQUE')
        ->assertDontSee('name="title"', false);

    expect(Announcement::query()->count())->toBe(1)
        ->and($plain->refresh()->admin)->toBeFalse();
});
