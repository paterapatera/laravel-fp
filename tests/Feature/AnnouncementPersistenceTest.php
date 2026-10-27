<?php

use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(LazilyRefreshDatabase::class);

test('DATA-ANN-NEW-1 各項目が保存され updated_at 列は存在しない', function () {
    $publishedStartsAt = now()->subHour()->startOfSecond();
    $publishedEndsAt = now()->addHour()->startOfSecond();
    $draftStartsAt = now()->subDays(2)->startOfSecond();
    $draftEndsAt = now()->subDay()->startOfSecond();

    $published = Announcement::factory()->create([
        'title' => '公開タイトル',
        'summary' => '公開要約',
        'body' => '公開本文',
        'status' => AnnouncementStatus::Published,
        'starts_at' => $publishedStartsAt,
        'ends_at' => $publishedEndsAt,
    ]);

    $draft = Announcement::factory()->draft()->create([
        'title' => '下書きタイトル',
        'summary' => '下書き要約',
        'body' => '下書き本文',
        'starts_at' => $draftStartsAt,
        'ends_at' => $draftEndsAt,
    ]);

    $storedPublished = Announcement::query()->findOrFail($published->id);
    $storedDraft = Announcement::query()->findOrFail($draft->id);
    $publishedRow = DB::table('announcements')->where('id', $published->id)->first();
    $draftRow = DB::table('announcements')->where('id', $draft->id)->first();

    expect($storedPublished->title)->toBe('公開タイトル')
        ->and($storedPublished->summary)->toBe('公開要約')
        ->and($storedPublished->body)->toBe('公開本文')
        ->and($storedPublished->status)->toBe(AnnouncementStatus::Published)
        ->and($publishedRow->status)->toBe('published')
        ->and($storedPublished->starts_at?->toDateTimeString())->toBe($publishedStartsAt->toDateTimeString())
        ->and($storedPublished->ends_at?->toDateTimeString())->toBe($publishedEndsAt->toDateTimeString())
        ->and($storedPublished->created_at)->not->toBeNull()
        ->and($publishedRow->created_at)->not->toBeNull()
        ->and($storedDraft->title)->toBe('下書きタイトル')
        ->and($storedDraft->summary)->toBe('下書き要約')
        ->and($storedDraft->body)->toBe('下書き本文')
        ->and($storedDraft->status)->toBe(AnnouncementStatus::Draft)
        ->and($draftRow->status)->toBe('draft')
        ->and($storedDraft->starts_at?->toDateTimeString())->toBe($draftStartsAt->toDateTimeString())
        ->and($storedDraft->ends_at?->toDateTimeString())->toBe($draftEndsAt->toDateTimeString())
        ->and($storedDraft->created_at)->not->toBeNull()
        ->and($draftRow->created_at)->not->toBeNull()
        ->and(Schema::hasColumn('announcements', 'updated_at'))->toBeFalse();
});
