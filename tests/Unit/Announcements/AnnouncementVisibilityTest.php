<?php

use App\Announcements\AnnouncementVisibility;
use App\Enums\AnnouncementStatus;

function visibleAt(
    AnnouncementStatus $status = AnnouncementStatus::Published,
    ?string $title = '題名',
    ?string $summary = '要約',
    ?string $body = '本文',
    mixed $startsAt = 'default',
    mixed $endsAt = 'default',
    ?DateTimeImmutable $now = null,
): bool {
    $now ??= new DateTimeImmutable('2026-06-01 12:00:00 UTC');

    if ($startsAt === 'default') {
        $startsAt = new DateTimeImmutable('2026-06-01 00:00:00 UTC');
    }

    if ($endsAt === 'default') {
        $endsAt = new DateTimeImmutable('2026-06-02 00:00:00 UTC');
    }

    return (new AnnouncementVisibility)->isVisible($status, $title, $summary, $body, $startsAt, $endsAt, $now);
}

test('REQ-ANN-1 REQ-ANN-8 開始の瞬間と終了の瞬間は公開対象である', function () {
    $start = new DateTimeImmutable('2026-06-01 12:00:00 UTC');
    $end = new DateTimeImmutable('2026-06-02 12:00:00 UTC');

    expect(visibleAt(startsAt: $start, endsAt: $end, now: $start))->toBeTrue()
        ->and(visibleAt(startsAt: $start, endsAt: $end, now: $end))->toBeTrue();
});

test('REQ-ANN-1 REQ-ANN-8 下書き、期間外、空の時刻、空白だけの項目は公開対象でない', function () {
    $now = new DateTimeImmutable('2026-06-01 12:00:00 UTC');
    $start = new DateTimeImmutable('2026-06-01 00:00:00 UTC');
    $end = new DateTimeImmutable('2026-06-02 00:00:00 UTC');

    expect(visibleAt(status: AnnouncementStatus::Draft, now: $now))->toBeFalse()
        ->and(visibleAt(now: new DateTimeImmutable('2026-05-31 23:59:59 UTC'), startsAt: $start, endsAt: $end))->toBeFalse()
        ->and(visibleAt(now: new DateTimeImmutable('2026-06-02 00:00:01 UTC'), startsAt: $start, endsAt: $end))->toBeFalse()
        ->and(visibleAt(startsAt: null, now: $now))->toBeFalse()
        ->and(visibleAt(endsAt: null, now: $now))->toBeFalse()
        ->and(visibleAt(title: null))->toBeFalse()
        ->and(visibleAt(title: ''))->toBeFalse()
        ->and(visibleAt(title: " \n\t"))->toBeFalse()
        ->and(visibleAt(title: "\u{3000}"))->toBeFalse()
        ->and(visibleAt(summary: '   '))->toBeFalse()
        ->and(visibleAt(body: "\u{00A0}"))->toBeFalse();
});
