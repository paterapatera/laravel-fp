<?php

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('REQ-ANR-12 REQ-ANR-13 空白の項目だけを示し、入力した文字を残して保存しない', function () {
    $admin = User::factory()->create(['admin' => true]);

    $this->actingAs($admin)->post(route('announcements.register.store'), [
        'title' => '<b>注意</b>',
        'summary' => '   ',
        'body' => '本文の文字UNIQUE',
    ])->assertRedirect(route('announcements.register.create'))
        ->assertSessionHasErrors(['summary' => '要約がありません']);

    $this->followingRedirects()->post(route('announcements.register.store'), [
        'title' => '<b>注意</b>',
        'summary' => '   ',
        'body' => '本文の文字UNIQUE',
    ])->assertOk()
        ->assertSee('要約がありません')
        ->assertDontSee('題名がありません')
        ->assertDontSee('本文がありません')
        ->assertSee('&lt;b&gt;注意&lt;/b&gt;', false)
        ->assertDontSee('<b>注意</b>', false)
        ->assertSee('本文の文字UNIQUE')
        ->assertSee('value="   "', false);

    expect(Announcement::query()->count())->toBe(0);

    $this->post(route('announcements.register.store'), [
        'title' => "\u{3000}",
        'summary' => '残す要約UNIQUE',
        'body' => '',
    ])->assertRedirect(route('announcements.register.create'))
        ->assertSessionHasErrors([
            'title' => '題名がありません',
            'body' => '本文がありません',
        ]);

    $this->followingRedirects()->post(route('announcements.register.store'), [
        'title' => "\u{3000}",
        'summary' => '残す要約UNIQUE',
        'body' => '',
    ])->assertOk()
        ->assertSee('題名がありません')
        ->assertSee('本文がありません')
        ->assertDontSee('要約がありません')
        ->assertSee('残す要約UNIQUE');

    expect(Announcement::query()->count())->toBe(0);
});

test('REQ-ANR-3 REQ-ANR-14 REQ-ANR-15 REQ-ANR-16 REQ-ANR-17 REQ-ANR-19 成功すると題名だけを文字で出し、下書きは期間なしで一件増える', function () {
    $admin = User::factory()->create(['admin' => true]);
    $title = '  <b>注意</b>  ';

    $response = $this->actingAs($admin)->post(route('announcements.register.store'), [
        'title' => $title,
        'summary' => '要約だけUNIQUE',
        'body' => '本文だけUNIQUE',
        'starts_at' => '2026-02-02 00:00:00',
        'ends_at' => '2026-01-01 00:00:00',
        'status' => AnnouncementStatus::Published->value,
        'admin' => '0',
    ]);

    $response->assertRedirect(route('announcements.register.created'));

    $this->get(route('announcements.register.created'))
        ->assertOk()
        ->assertSee('&lt;b&gt;注意&lt;/b&gt;', false)
        ->assertDontSee('<b>注意</b>', false)
        ->assertDontSee('要約だけUNIQUE')
        ->assertDontSee('本文だけUNIQUE')
        ->assertDontSee('href="'.route('announcements.register.create').'"', false)
        ->assertDontSee('登録');

    $draft = Announcement::query()->sole();

    expect($draft->status)->toBe(AnnouncementStatus::Draft)
        ->and($draft->title)->toBe($title)
        ->and($draft->summary)->toBe('要約だけUNIQUE')
        ->and($draft->body)->toBe('本文だけUNIQUE')
        ->and($draft->starts_at)->toBeNull()
        ->and($draft->ends_at)->toBeNull()
        ->and($admin->refresh()->admin)->toBeTrue();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('&lt;b&gt;注意&lt;/b&gt;', false)
        ->assertDontSee($title, false);
});

test('REQ-ANR-14 空の要約を補うと下書きが一件増え、別の登録はもう一件増える', function () {
    $admin = User::factory()->create(['admin' => true]);

    $this->actingAs($admin)->post(route('announcements.register.store'), [
        'title' => '補完前の題名',
        'summary' => '',
        'body' => '補完前の本文',
    ])->assertRedirect(route('announcements.register.create'));

    expect(Announcement::query()->count())->toBe(0);

    $this->post(route('announcements.register.store'), [
        'title' => '補完前の題名',
        'summary' => '補った要約',
        'body' => '補完前の本文',
    ])->assertRedirect(route('announcements.register.created'));

    expect(Announcement::query()->where('status', AnnouncementStatus::Draft)->count())->toBe(1);

    $this->post(route('announcements.register.store'), [
        'title' => '二つ目の題名',
        'summary' => '二つ目の要約',
        'body' => '二つ目の本文',
    ])->assertRedirect(route('announcements.register.created'));

    expect(Announcement::query()->where('status', AnnouncementStatus::Draft)->count())->toBe(2);
});

test('REQ-ANR-6 REQ-ANR-8 REQ-ANR-10 REQ-ANR-19 管理者以外の登録はお知らせを増やさず、送られた管理者フラグでも admin は変わらない', function () {
    Announcement::factory()->create();
    $plain = User::factory()->create();

    $this->post(route('announcements.register.store'), [
        'title' => '未ログイン題名UNIQUE',
        'summary' => '未ログイン要約UNIQUE',
        'body' => '未ログイン本文UNIQUE',
        'admin' => '1',
    ])->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('未ログイン題名UNIQUE');

    expect(Announcement::query()->count())->toBe(1);

    $this->actingAs($plain)->post(route('announcements.register.store'), [
        'title' => '非管理者題名UNIQUE',
        'summary' => '非管理者要約UNIQUE',
        'body' => '非管理者本文UNIQUE',
        'admin' => '1',
    ])->assertNotFound()
        ->assertContent('')
        ->assertDontSee('非管理者題名UNIQUE')
        ->assertDontSee('非管理者本文UNIQUE');

    expect(Announcement::query()->count())->toBe(1)
        ->and($plain->refresh()->admin)->toBeFalse();
});

test('REQ-ANR-18 成功画面を再表示しても、その題名の下書きは一件のままである', function () {
    $admin = User::factory()->create(['admin' => true]);

    $this->actingAs($admin)->post(route('announcements.register.store'), [
        'title' => '再送されない題名',
        'summary' => '再送されない要約',
        'body' => '再送されない本文',
    ])->assertRedirect(route('announcements.register.created'));

    $this->get(route('announcements.register.created'))
        ->assertOk()
        ->assertSee('再送されない題名');

    $this->get(route('announcements.register.created'))->assertOk();

    expect(Announcement::query()->where('title', '再送されない題名')->count())->toBe(1);
});
