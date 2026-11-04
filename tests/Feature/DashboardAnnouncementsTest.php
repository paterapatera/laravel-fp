<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(LazilyRefreshDatabase::class);

/**
 * @param  list<string>  $shown
 * @param  list<string>  $hidden
 * @param  list<string>  $summaries
 */
function assertDashboardAnnouncementPrefix(TestResponse $response, array $shown, array $hidden, array $summaries): void
{
    $response->assertOk()
        ->assertSeeInOrder($shown)
        ->assertSeeInOrder($summaries)
        ->assertDontSee('続き')
        ->assertDontSee('全件')
        ->assertDontSee('残り')
        ->assertDontSee('件');

    foreach ($hidden as $title) {
        $response->assertDontSee($title);
    }

    expect(substr_count($response->getContent(), '<h2'))->toBe(count($shown));
}

/**
 * @return array{shown: list<string>, hidden: list<string>, summaries: list<string>}
 */
function announcementsBeyondTheListLimit(): array
{
    $now = now()->startOfSecond();
    $shown = [];
    $hidden = [];
    $summaries = [];

    for ($index = 0; $index < 12; $index++) {
        $title = sprintf('開始順%02d', $index);
        $summary = sprintf('開始要約%02d', $index);

        Announcement::factory()->create([
            'title' => $title,
            'summary' => $summary,
            'starts_at' => $now->copy()->subMinutes(12 - $index),
            'ends_at' => $now->copy()->addDay(),
        ]);

        if ($index >= 2) {
            $shown[] = $title;
            $summaries[] = $summary;
        } else {
            $hidden[] = $title;
            $hidden[] = $summary;
        }
    }

    return [
        'shown' => array_reverse($shown),
        'hidden' => $hidden,
        'summaries' => array_reverse($summaries),
    ];
}

test('REQ-ANN-1 REQ-ANN-3 REQ-ANN-5 REQ-ANN-10 一覧はタイトルと要約だけを出し、空ならその旨を示し、作成・編集・削除は無い', function () {
    $user = User::factory()->create();
    $visible = Announcement::factory()->create([
        'title' => 'Visible <b>title</b>',
        'summary' => '短い要約',
        'body' => '一覧に出ない本文UNIQUE',
    ]);
    Announcement::factory()->draft()->create([
        'title' => '下書きタイトルUNIQUE',
        'summary' => '下書き要約',
        'body' => '下書き本文',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Visible &lt;b&gt;title&lt;/b&gt;', false)
        ->assertSee('短い要約')
        ->assertSee(route('announcements.show', $visible), false)
        ->assertDontSee('Visible <b>title</b>', false)
        ->assertDontSee('一覧に出ない本文UNIQUE')
        ->assertDontSee('下書きタイトルUNIQUE')
        ->assertDontSee('作成')
        ->assertDontSee('編集')
        ->assertDontSee('削除');

    Announcement::query()->delete();

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('お知らせはありません。')
        ->assertDontSee('announcements/', false);

    Announcement::factory()->draft()->create([
        'title' => '残った下書きUNIQUE',
    ]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('お知らせはありません。')
        ->assertDontSee('残った下書きUNIQUE');
});

test('REQ-ANN-2 二人のログイン済み利用者に同じ一覧が出る', function () {
    Announcement::factory()->create(['title' => '共通のお知らせ一']);
    Announcement::factory()->create(['title' => '共通のお知らせ二']);

    $first = $this->actingAs(User::factory()->create())->get(route('dashboard'));
    $second = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $first->assertOk()->assertSee('共通のお知らせ一')->assertSee('共通のお知らせ二');
    $second->assertOk()->assertSee('共通のお知らせ一')->assertSee('共通のお知らせ二');
});

test('REQ-ANN-4 ダッシュボードは公開開始、作成、タイトルの順に並ぶ', function () {
    $now = now()->startOfSecond();
    $sameStart = $now->copy()->subDay();

    $olderStart = Announcement::factory()->create([
        'title' => '開始が古い',
        'summary' => '古い要約',
        'starts_at' => $now->copy()->subDays(3),
        'ends_at' => $now->copy()->addDay(),
    ]);
    $olderStart->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $newerCreated = Announcement::factory()->create([
        'title' => '作成が新しい',
        'summary' => '新しい要約',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $newerCreated->forceFill(['created_at' => $now->copy()->subMinutes(10)])->save();

    $laterTitle = Announcement::factory()->create([
        'title' => 'mango',
        'summary' => '後の要約',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $laterTitle->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $earlierTitle = Announcement::factory()->create([
        'title' => 'apple',
        'summary' => '先の要約',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $earlierTitle->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['作成が新しい', 'apple', 'mango', '開始が古い']);
});

test('REQ-ANN-12 NFR-ANN-1 開始が新しい公開対象の先頭10件だけを出す', function () {
    $prefix = announcementsBeyondTheListLimit();

    $response = $this->actingAs(User::factory()->create())->get('/dashboard');

    assertDashboardAnnouncementPrefix($response, $prefix['shown'], $prefix['hidden'], $prefix['summaries']);
});

test('REQ-ANN-12 公開対象が10件未満ならその全件を順に出す', function () {
    $now = now()->startOfSecond();

    foreach ([
        ['少数丙', 3, '要約丙'],
        ['少数乙', 2, '要約乙'],
        ['少数甲', 1, '要約甲'],
    ] as [$title, $minutesAgo, $summary]) {
        Announcement::factory()->create([
            'title' => $title,
            'summary' => $summary,
            'starts_at' => $now->copy()->subMinutes($minutesAgo),
            'ends_at' => $now->copy()->addDay(),
        ]);
    }

    assertDashboardAnnouncementPrefix(
        $this->actingAs(User::factory()->create())->get('/dashboard'),
        ['少数甲', '少数乙', '少数丙'],
        [],
        ['要約甲', '要約乙', '要約丙'],
    );
});

test('REQ-ANN-12 公開対象がちょうど10件ならその全件を順に出す', function () {
    $now = now()->startOfSecond();
    $shown = [];
    $summaries = [];

    for ($index = 0; $index < 10; $index++) {
        $shown[] = sprintf('同数%02d', $index);
        $summaries[] = sprintf('同数要約%02d', $index);
        Announcement::factory()->create([
            'title' => $shown[$index],
            'summary' => $summaries[$index],
            'starts_at' => $now->copy()->subMinutes(10 - $index),
            'ends_at' => $now->copy()->addDay(),
        ]);
    }

    assertDashboardAnnouncementPrefix(
        $this->actingAs(User::factory()->create())->get('/dashboard'),
        array_reverse($shown),
        [],
        array_reverse($summaries),
    );
});

test('REQ-ANN-12 下書きは先頭10件の枠を使わない', function () {
    $now = now()->startOfSecond();
    $hidden = [];

    for ($index = 0; $index < 11; $index++) {
        $title = sprintf('下書き枠%02d', $index);
        $summary = sprintf('下書き要約%02d', $index);
        $hidden[] = $title;
        $hidden[] = $summary;
        Announcement::factory()->draft()->create([
            'title' => $title,
            'summary' => $summary,
            'starts_at' => $now->copy()->subMinutes($index + 1),
            'ends_at' => $now->copy()->addDay(),
        ]);
    }

    $shown = [];
    $summaries = [];

    for ($index = 0; $index < 4; $index++) {
        $shown[] = sprintf('公開枠%02d', $index);
        $summaries[] = sprintf('公開要約%02d', $index);
        Announcement::factory()->create([
            'title' => $shown[$index],
            'summary' => $summaries[$index],
            'starts_at' => $now->copy()->subMinutes(4 - $index),
            'ends_at' => $now->copy()->addDay(),
        ]);
    }

    assertDashboardAnnouncementPrefix(
        $this->actingAs(User::factory()->create())->get('/dashboard'),
        array_reverse($shown),
        $hidden,
        array_reverse($summaries),
    );
});

test('REQ-ANN-12 作成が新しい公開対象の先頭10件を出す', function () {
    $now = now()->startOfSecond();
    $start = $now->copy()->subDay();
    $shown = [];
    $hidden = [];
    $summaries = [];

    for ($index = 0; $index < 12; $index++) {
        $title = sprintf('作成順%02d', $index);
        $summary = sprintf('作成要約%02d', $index);
        $announcement = Announcement::factory()->create([
            'title' => $title,
            'summary' => $summary,
            'starts_at' => $start,
            'ends_at' => $now->copy()->addDay(),
        ]);
        $announcement->forceFill([
            'created_at' => $now->copy()->subMinutes(12 - $index),
        ])->save();

        if ($index >= 2) {
            $shown[] = $title;
            $summaries[] = $summary;
        } else {
            $hidden[] = $title;
            $hidden[] = $summary;
        }
    }

    assertDashboardAnnouncementPrefix(
        $this->actingAs(User::factory()->create())->get('/dashboard'),
        array_reverse($shown),
        $hidden,
        array_reverse($summaries),
    );
});

test('REQ-ANN-12 タイトルの文字符号点が小さい公開対象の先頭10件を出す', function () {
    $now = now()->startOfSecond();
    $start = $now->copy()->subDay();
    $created = $now->copy()->subHour();
    $shown = [];
    $hidden = [];
    $summaries = [];

    for ($index = 11; $index >= 0; $index--) {
        $title = sprintf('n%02d', $index);
        $summary = sprintf('符号要約%02d', $index);
        $announcement = Announcement::factory()->create([
            'title' => $title,
            'summary' => $summary,
            'starts_at' => $start,
            'ends_at' => $now->copy()->addDay(),
        ]);
        $announcement->forceFill(['created_at' => $created])->save();

        if ($index < 10) {
            $shown[] = $title;
            $summaries[] = $summary;
        } else {
            $hidden[] = $title;
            $hidden[] = $summary;
        }
    }

    sort($shown);
    sort($summaries);

    assertDashboardAnnouncementPrefix(
        $this->actingAs(User::factory()->create())->get('/dashboard'),
        $shown,
        $hidden,
        $summaries,
    );
});

test('REQ-ANN-12 開始と作成時刻とタイトルが同じでも10件に限る', function () {
    $now = now()->startOfSecond();
    $start = $now->copy()->subDay();
    $created = $now->copy()->subHour();

    for ($index = 0; $index < 12; $index++) {
        $announcement = Announcement::factory()->create([
            'title' => '同点題名',
            'summary' => '同点要約',
            'starts_at' => $start,
            'ends_at' => $now->copy()->addDay(),
        ]);
        $announcement->forceFill(['created_at' => $created])->save();
    }

    $response = $this->actingAs(User::factory()->create())->get('/dashboard');

    $response->assertOk()
        ->assertSee('同点題名')
        ->assertSee('同点要約')
        ->assertDontSee('続き')
        ->assertDontSee('全件')
        ->assertDontSee('残り')
        ->assertDontSee('件');

    expect(substr_count($response->getContent(), '<h2'))->toBe(10)
        ->and(substr_count($response->getContent(), '同点題名'))->toBe(10);
});

test('REQ-ANN-12 REQ-ANN-13 REQ-ANN-14 各入口は同じ先頭10件を出し11件目以降と省略の知らせは出さない', function () {
    $prefix = announcementsBeyondTheListLimit();
    $user = User::factory()->create();

    assertDashboardAnnouncementPrefix(
        $this->actingAs($user)->get('/dashboard'),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );

    $welcome = $this->actingAs($user)->get('/');
    $welcome->assertOk()->assertSee('href="'.route('dashboard').'"', false);

    assertDashboardAnnouncementPrefix(
        $this->actingAs($user)->get(route('dashboard')),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );

    $this->post('/logout')->assertRedirect(route('welcome'));
    $this->assertGuest();

    assertDashboardAnnouncementPrefix(
        $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );

    $this->post('/logout')->assertRedirect(route('welcome'));
    $this->assertGuest();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertRedirect(route('login'));
    $this->assertGuest();

    assertDashboardAnnouncementPrefix(
        $this->followingRedirects()->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );

    assertDashboardAnnouncementPrefix(
        $this->actingAs($user)->followingRedirects()->get('/login'),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );
});

test('REQ-ANN-12 管理者とそれ以外には同じ先頭10件が出る', function () {
    $prefix = announcementsBeyondTheListLimit();
    $admin = User::factory()->create(['admin' => true]);
    $member = User::factory()->create();

    assertDashboardAnnouncementPrefix(
        $this->actingAs($admin)->get('/dashboard'),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );
    assertDashboardAnnouncementPrefix(
        $this->actingAs($member)->get('/dashboard'),
        $prefix['shown'],
        $prefix['hidden'],
        $prefix['summaries'],
    );
});

test('REQ-ANN-1 REQ-ANN-14 公開対象が0件のときは空の文言だけで省略の知らせは無い', function () {
    $response = $this->actingAs(User::factory()->create())->get('/dashboard');

    $response->assertOk()
        ->assertSee('お知らせはありません。')
        ->assertDontSee('続き')
        ->assertDontSee('全件')
        ->assertDontSee('残り')
        ->assertDontSee('件');

    expect(substr_count($response->getContent(), '<h2'))->toBe(0);
});
