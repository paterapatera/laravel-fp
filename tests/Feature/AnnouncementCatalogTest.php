<?php

use App\Announcements\AnnouncementCatalog;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('REQ-ANN-1 REQ-ANN-8 公開対象だけが一覧と一件取得に残り、それ以外は失敗する', function () {
    $now = now()->startOfSecond();
    $visible = Announcement::factory()->create([
        'title' => '公開対象',
        'summary' => '要約',
        'body' => '本文',
        'starts_at' => $now,
        'ends_at' => $now,
    ]);
    $draft = Announcement::factory()->draft()->create([
        'title' => '下書き',
        'body' => '下書き本文',
    ]);
    $ended = Announcement::factory()->create([
        'title' => '終了後',
        'starts_at' => $now->copy()->subDays(2),
        'ends_at' => $now->copy()->subSecond(),
    ]);
    $blankSummary = Announcement::factory()->create([
        'title' => '要約が空白',
        'summary' => "\u{3000}",
    ]);
    $missingEnd = Announcement::factory()->create([
        'title' => '終了なし',
        'ends_at' => null,
    ]);

    $catalog = app(AnnouncementCatalog::class);
    $titles = $catalog->visible($now)->get()->pluck('title')->all();

    expect($titles)->toBe(['公開対象'])
        ->and($catalog->findVisible($visible->id, $now)->isOk())->toBeTrue()
        ->and($catalog->findVisible($draft->id, $now)->isErr())->toBeTrue()
        ->and($catalog->findVisible($ended->id, $now)->isErr())->toBeTrue()
        ->and($catalog->findVisible($blankSummary->id, $now)->isErr())->toBeTrue()
        ->and($catalog->findVisible($missingEnd->id, $now)->isErr())->toBeTrue()
        ->and($catalog->findVisible($visible->id + 1000, $now)->isErr())->toBeTrue();
});

test('REQ-ANN-2 利用者を変えても公開対象の一覧は同じである', function () {
    Announcement::factory()->create(['title' => '一件目']);
    Announcement::factory()->create(['title' => '二件目']);
    $catalog = app(AnnouncementCatalog::class);
    $now = now();

    $this->actingAs(User::factory()->create());
    $first = $catalog->visible($now)->get()->pluck('title')->sort()->values()->all();

    $this->actingAs(User::factory()->create());
    $second = $catalog->visible($now)->get()->pluck('title')->sort()->values()->all();

    expect($first)->toBe(['一件目', '二件目'])
        ->and($second)->toBe($first);
});

test('REQ-ANN-4 公開開始、作成、タイトルの順で並ぶ', function () {
    $now = now()->startOfSecond();
    $sameStart = $now->copy()->subDay();

    $olderStart = Announcement::factory()->create([
        'title' => '開始が古い',
        'starts_at' => $now->copy()->subDays(3),
        'ends_at' => $now->copy()->addDay(),
    ]);
    $olderStart->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $newerCreated = Announcement::factory()->create([
        'title' => '作成が新しい',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $newerCreated->forceFill(['created_at' => $now->copy()->subMinutes(10)])->save();

    $titleLater = Announcement::factory()->create([
        'title' => 'mango',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $titleLater->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $titleEarlier = Announcement::factory()->create([
        'title' => 'apple',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $titleEarlier->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $titles = app(AnnouncementCatalog::class)->visible($now)->get()->pluck('title')->all();

    expect($titles)->toBe(['作成が新しい', 'apple', 'mango', '開始が古い']);
});
