<?php

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('REQ-ANN-NEW-6 REQ-ANN-NEW-7 REQ-ANN-NEW-8 REQ-ANN-NEW-10 詳細はタイトルと本文だけを出し、公開対象でなければ本文の無い 404 で、作成・編集・削除は無い', function () {
    $user = User::factory()->create();
    $now = now()->startOfSecond();
    $first = Announcement::factory()->create([
        'title' => '一件目 <i>題</i>',
        'summary' => '一件目だけの要約UNIQUE',
        'body' => '一件目の本文UNIQUE',
    ]);
    $second = Announcement::factory()->create([
        'title' => '二件目',
        'summary' => '二件目の要約',
        'body' => '二件目の本文UNIQUE',
    ]);
    $draft = Announcement::factory()->draft()->create([
        'title' => '下書き題',
        'body' => '下書き本文UNIQUE',
    ]);
    $beforeStart = Announcement::factory()->create([
        'title' => '開始前',
        'body' => '開始前本文UNIQUE',
        'starts_at' => $now->copy()->addMinute(),
        'ends_at' => $now->copy()->addDay(),
    ]);
    $afterEnd = Announcement::factory()->create([
        'title' => '終了後',
        'body' => '終了後本文UNIQUE',
        'starts_at' => $now->copy()->subDays(2),
        'ends_at' => $now->copy()->subSecond(),
    ]);
    $blankBody = Announcement::factory()->create([
        'title' => '本文が空白',
        'body' => '   ',
    ]);
    $missingStart = Announcement::factory()->create([
        'title' => '開始なし',
        'body' => '開始なし本文UNIQUE',
        'starts_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('announcements.show', $first))
        ->assertOk()
        ->assertSee('一件目 &lt;i&gt;題&lt;/i&gt;', false)
        ->assertSee('一件目の本文UNIQUE')
        ->assertDontSee('一件目 <i>題</i>', false)
        ->assertDontSee('一件目だけの要約UNIQUE')
        ->assertDontSee('二件目の本文UNIQUE')
        ->assertDontSee('作成')
        ->assertDontSee('編集')
        ->assertDontSee('削除')
        ->assertSee('ログアウト');

    $this->actingAs($user)->get(route('announcements.show', $second->id + 1000))->assertNotFound();

    foreach ([$draft, $beforeStart, $afterEnd, $blankBody, $missingStart] as $hidden) {
        $this->actingAs($user)
            ->get(route('announcements.show', $hidden))
            ->assertNotFound()
            ->assertDontSee((string) $hidden->title);
    }

    $this->actingAs($user)
        ->get(route('announcements.show', $draft))
        ->assertDontSee('下書き本文UNIQUE');
    $this->actingAs($user)
        ->get(route('announcements.show', $beforeStart))
        ->assertDontSee('開始前本文UNIQUE');
    $this->actingAs($user)
        ->get(route('announcements.show', $afterEnd))
        ->assertDontSee('終了後本文UNIQUE');
    $this->actingAs($user)
        ->get(route('announcements.show', $missingStart))
        ->assertDontSee('開始なし本文UNIQUE');

    $this->actingAs($user)->get('/announcements/not-a-number')->assertNotFound();
});

test('REQ-ANN-NEW-9 一覧の二件目を選ぶと二件目の詳細が出る', function () {
    $now = now()->startOfSecond();
    $sameStart = $now->copy()->subDay();

    $second = Announcement::factory()->create([
        'title' => '二件目の題',
        'summary' => '二件目の要約',
        'body' => '二件目の本文だけ',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $second->forceFill(['created_at' => $now->copy()->subHours(2)])->save();

    $first = Announcement::factory()->create([
        'title' => '一件目の題',
        'summary' => '一件目の要約',
        'body' => '一件目の本文だけ',
        'starts_at' => $sameStart,
        'ends_at' => $now->copy()->addDay(),
    ]);
    $first->forceFill(['created_at' => $now->copy()->subHour()])->save();

    $list = $this->actingAs(User::factory()->create())->get(route('dashboard'));
    $list->assertSeeInOrder(['一件目の題', '二件目の題']);

    preg_match_all('/href="([^"]*\/announcements\/\d+)"/', $list->getContent(), $matches);
    $secondHref = html_entity_decode($matches[1][1]);

    $this->get($secondHref)
        ->assertOk()
        ->assertSee('二件目の題')
        ->assertSee('二件目の本文だけ')
        ->assertDontSee('一件目の本文だけ');
});

test('REQ-DSH-NEW-1 REQ-DSH-NEW-5 REQ-DSH-NEW-9 REQ-DSH-NEW-10 未ログインの詳細はログインへ戻り、成功後にその詳細を示し、詳細からログアウトできる', function () {
    $user = User::factory()->create();
    $announcement = Announcement::factory()->create([
        'title' => '戻る詳細',
        'summary' => '戻る要約',
        'body' => '戻る本文UNIQUE',
    ]);

    $this->get(route('announcements.show', $announcement))
        ->assertRedirect(route('login'));

    $this->get(route('login'))
        ->assertDontSee('戻る本文UNIQUE')
        ->assertDontSee('戻る詳細');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('announcements.show', $announcement));

    $this->get(route('announcements.show', $announcement))
        ->assertOk()
        ->assertSee('戻る本文UNIQUE')
        ->assertSee('ログアウト');

    $this->post(route('logout'))
        ->assertRedirect(route('welcome'));

    $this->get(route('welcome'))
        ->assertOk()
        ->assertSee('ログイン')
        ->assertDontSee('戻る本文UNIQUE');

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
