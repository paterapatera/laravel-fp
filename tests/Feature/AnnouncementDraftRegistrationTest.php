<?php

use App\Announcements\AnnouncementRegistrar;
use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('REQ-ANR-NEW-14 REQ-ANR-NEW-15 下書きを一件追加し、期間は空で、一覧には題名が出ない', function () {
    $title = '  下書き題名UNIQUE  ';
    $summary = '  要約 <b>注意</b>  ';
    $body = " 本文\n";

    $result = (new AnnouncementRegistrar)->register($title, $summary, $body);
    $draft = Announcement::query()->sole();

    expect($result->isOk())->toBeTrue()
        ->and($result->get()->is($draft))->toBeTrue()
        ->and($draft->status)->toBe(AnnouncementStatus::Draft)
        ->and($draft->title)->toBe($title)
        ->and($draft->summary)->toBe($summary)
        ->and($draft->body)->toBe($body)
        ->and($draft->starts_at)->toBeNull()
        ->and($draft->ends_at)->toBeNull();

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee($title);
});

test('REQ-ANR-NEW-14 二回登録すると下書きがもう一件増える', function () {
    $registrar = new AnnouncementRegistrar;

    $registrar->register('一つ目の題', '一つ目の要約', '一つ目の本文');
    $registrar->register('二つ目の題', '二つ目の要約', '二つ目の本文');

    expect(Announcement::query()->where('status', AnnouncementStatus::Draft)->count())->toBe(2)
        ->and(Announcement::query()->count())->toBe(2);
});
