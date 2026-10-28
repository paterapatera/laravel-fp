<?php

use App\Announcements\AnnouncementRegistrar;
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
