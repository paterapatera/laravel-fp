<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(LazilyRefreshDatabase::class);

test('REQ-ANN-NEW-11 変更を求めても保存済みのタイトル、要約、本文は変わらない', function () {
    $announcement = Announcement::factory()->create([
        'title' => '保存済みタイトル',
        'summary' => '保存済み要約',
        'body' => '保存済み本文',
    ]);

    expect(Route::has('announcements.store'))->toBeFalse()
        ->and(Route::has('announcements.update'))->toBeFalse()
        ->and(Route::has('announcements.destroy'))->toBeFalse()
        ->and(Route::has('announcements.edit'))->toBeFalse();

    $this->actingAs(User::factory()->create());

    foreach (['post', 'put', 'patch', 'delete'] as $method) {
        $this->{$method}('/announcements/'.$announcement->id, [
            'title' => '書き換え',
            'summary' => '書き換え要約',
            'body' => '書き換え本文',
        ])->assertMethodNotAllowed();
    }

    $stored = $announcement->fresh();

    expect($stored)->not->toBeNull()
        ->and($stored->title)->toBe('保存済みタイトル')
        ->and($stored->summary)->toBe('保存済み要約')
        ->and($stored->body)->toBe('保存済み本文')
        ->and(Announcement::query()->count())->toBe(1);
});
