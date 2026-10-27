<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

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
