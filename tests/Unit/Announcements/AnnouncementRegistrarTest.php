<?php

use App\Announcements\AnnouncementRegistrar;
use App\Enums\AnnouncementStatus;
use App\Models\Announcement;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, LazilyRefreshDatabase::class);

test('REQ-ANR-11 空白だけの題名・要約・本文は保存しない', function () {
    $registrar = new AnnouncementRegistrar;
    $blanks = ['', ' ', " \n\t", "\u{3000}", "\u{00A0}"];

    foreach ($blanks as $blank) {
        $title = $registrar->register($blank, '要約', '本文');
        $summary = $registrar->register('題名', $blank, '本文');
        $body = $registrar->register('題名', '要約', $blank);

        expect($title->isErr())->toBeTrue()
            ->and($title->error())->toBe(['title'])
            ->and($summary->isErr())->toBeTrue()
            ->and($summary->error())->toBe(['summary'])
            ->and($body->isErr())->toBeTrue()
            ->and($body->error())->toBe(['body']);
    }

    $allBlank = $registrar->register("\u{3000}", "\n", "\u{00A0}");

    expect($allBlank->isErr())->toBeTrue()
        ->and($allBlank->error())->toBe(['title', 'summary', 'body'])
        ->and(Announcement::query()->count())->toBe(0);
});

test('REQ-ANR-11 空白以外の文字は前後の空白もマークアップもそのまま保存する', function () {
    $title = '  あ  ';
    $summary = '<b>注意</b>';
    $body = " \t本文\n";

    $result = (new AnnouncementRegistrar)->register($title, $summary, $body);
    $stored = Announcement::query()->sole();

    expect($result->isOk())->toBeTrue()
        ->and($result->get()->is($stored))->toBeTrue()
        ->and($stored->title)->toBe($title)
        ->and($stored->summary)->toBe($summary)
        ->and($stored->body)->toBe($body)
        ->and(Announcement::query()->count())->toBe(1);
});

test('REQ-ANR-14 REQ-ANR-15 REQ-ANR-NEW-1 空の期間は空のまま、終了が開始以降の時は UTC の下書きを一件残す', function () {
    $registrar = new AnnouncementRegistrar;

    $empty = $registrar->register('題名', '要約', '本文', '   ', "\n");
    $emptyDraft = Announcement::query()->sole();

    expect($empty->isOk())->toBeTrue()
        ->and($emptyDraft->status)->toBe(AnnouncementStatus::Draft)
        ->and($emptyDraft->starts_at)->toBeNull()
        ->and($emptyDraft->ends_at)->toBeNull();

    $later = $registrar->register('別の題名', '別の要約', '別の本文', '2026-11-01 10', '2026-11-02 18');
    $laterDraft = Announcement::query()->where('title', '別の題名')->sole();

    expect($later->isOk())->toBeTrue()
        ->and($laterDraft->status)->toBe(AnnouncementStatus::Draft)
        ->and($laterDraft->starts_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-01 10:00:00')
        ->and($laterDraft->ends_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-02 18:00:00')
        ->and(Announcement::query()->where('status', AnnouncementStatus::Draft)->count())->toBe(2);

    $same = $registrar->register('同じ時', '同じ要約', '同じ本文', '2026-11-01 10', '2026-11-01 10');
    $sameDraft = Announcement::query()->where('title', '同じ時')->sole();

    expect($same->isOk())->toBeTrue()
        ->and($sameDraft->starts_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-01 10:00:00')
        ->and($sameDraft->ends_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-01 10:00:00');

    $edges = $registrar->register('端の時', '端の要約', '端の本文', '2026-11-01 0', '2026-11-01 23');
    $edgesDraft = Announcement::query()->where('title', '端の時')->sole();

    expect($edges->isOk())->toBeTrue()
        ->and($edgesDraft->starts_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-01 00:00:00')
        ->and($edgesDraft->ends_at?->utc()->format('Y-m-d H:i:s'))->toBe('2026-11-01 23:00:00');
});

test('REQ-ANR-NEW-2 REQ-ANR-NEW-4 REQ-ANR-NEW-6 片方だけ、逆転、時でない期間は追加しない', function () {
    $registrar = new AnnouncementRegistrar;

    $missingEnd = $registrar->register('題名', '要約', '本文', '2026-11-01 10', '');
    $whitespaceStart = $registrar->register('題名', '要約', '本文', '   ', '2026-11-02 18');
    $reversed = $registrar->register('題名', '要約', '本文', '2026-11-02 10', '2026-11-01 10');
    $minutes = $registrar->register('題名', '要約', '本文', '2026-11-01 10:30', '2026-11-02 18');
    $zeroMinutes = $registrar->register('題名', '要約', '本文', '2026-11-01 10:00', '2026-11-02 18');
    $hour24 = $registrar->register('題名', '要約', '本文', '2026-11-01 24', '2026-11-02 18');
    $dateOnly = $registrar->register('題名', '要約', '本文', '2026-11-01', '2026-11-02 18');
    $missingDay = $registrar->register('題名', '要約', '本文', '2026-02-31 10', '2026-11-02 18');
    $offset = $registrar->register('題名', '要約', '本文', '明日', '2026-11-01 10+09:00');
    $missingStart = $registrar->register('題名', '要約', '本文', '', '明日');
    $blankTitle = $registrar->register('', '要約', '本文', '2026-11-02 10', '2026-11-01 10');

    expect($missingEnd->isErr())->toBeTrue()
        ->and($missingEnd->error())->toBe(['ends_at'])
        ->and($whitespaceStart->isErr())->toBeTrue()
        ->and($whitespaceStart->error())->toBe(['starts_at'])
        ->and($reversed->isErr())->toBeTrue()
        ->and($reversed->error())->toBe(['window'])
        ->and($minutes->isErr())->toBeTrue()
        ->and($minutes->error())->toBe(['starts_at_invalid'])
        ->and($zeroMinutes->isErr())->toBeTrue()
        ->and($zeroMinutes->error())->toBe(['starts_at_invalid'])
        ->and($hour24->isErr())->toBeTrue()
        ->and($hour24->error())->toBe(['starts_at_invalid'])
        ->and($dateOnly->isErr())->toBeTrue()
        ->and($dateOnly->error())->toBe(['starts_at_invalid'])
        ->and($missingDay->isErr())->toBeTrue()
        ->and($missingDay->error())->toBe(['starts_at_invalid'])
        ->and($offset->isErr())->toBeTrue()
        ->and($offset->error())->toBe(['starts_at_invalid', 'ends_at_invalid'])
        ->and($missingStart->isErr())->toBeTrue()
        ->and($missingStart->error())->toBe(['starts_at', 'ends_at_invalid'])
        ->and($blankTitle->isErr())->toBeTrue()
        ->and($blankTitle->error())->toBe(['title', 'window'])
        ->and(Announcement::query()->count())->toBe(0);

    $fixed = $registrar->register('題名', '要約', '本文', '2026-11-01 10', '2026-11-02 18');

    expect($fixed->isOk())->toBeTrue()
        ->and(Announcement::query()->where('status', AnnouncementStatus::Draft)->count())->toBe(1);
});
