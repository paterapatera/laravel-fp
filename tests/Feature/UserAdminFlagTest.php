<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

test('DATA-ANR-NEW-1 管理者フラグは指定時に保存され未指定時は false になり fill では変わらない', function () {
    $admin = User::factory()->create(['admin' => true]);
    $plain = User::factory()->create();

    $storedAdmin = User::query()->findOrFail($admin->id);
    $adminRow = DB::table('users')->where('id', $admin->id)->first();
    $plainRow = DB::table('users')->where('id', $plain->id)->first();

    $plain->fill(['admin' => true]);

    expect($storedAdmin->admin)->toBeTrue()
        ->and((bool) $adminRow->admin)->toBeTrue()
        ->and((bool) $plainRow->admin)->toBeFalse()
        ->and($plain->admin)->toBeFalse()
        ->and($plain->isDirty('admin'))->toBeFalse();
});
