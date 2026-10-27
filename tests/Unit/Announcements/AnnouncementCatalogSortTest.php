<?php

use App\Announcements\AnnouncementCatalog;
use App\Announcements\AnnouncementVisibility;

test('REQ-ANN-NEW-4 開始と作成が同じときタイトルは strcmp の昇順になる', function () {
    $catalog = new AnnouncementCatalog(new AnnouncementVisibility);
    $instant = new DateTimeImmutable('2026-06-01 12:00:00 UTC');

    $order = $catalog->compareOrder(
        ['startsAt' => $instant, 'createdAt' => $instant, 'title' => 'mango'],
        ['startsAt' => $instant, 'createdAt' => $instant, 'title' => 'apple'],
    );

    expect($order)->toBe(strcmp('mango', 'apple'))
        ->and($order)->toBeGreaterThan(0)
        ->and($catalog->compareOrder(
            ['startsAt' => $instant, 'createdAt' => $instant, 'title' => 'あ'],
            ['startsAt' => $instant, 'createdAt' => $instant, 'title' => 'い'],
        ))->toBe(strcmp('あ', 'い'));
});
