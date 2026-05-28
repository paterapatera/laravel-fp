<?php

use App\Contracts\LogEntry;
use App\Support\Exceptions\ResultUnwrapException;
use App\Support\Result;
use Illuminate\Support\Collection;

test('ok は成功状態であり失敗状態ではない', function () {
    $result = Result::ok(42);

    expect($result->isOk())->toBeTrue()
        ->and($result->isErr())->toBeFalse();
});

test('err は失敗状態であり成功状態ではない', function () {
    $result = Result::err('reason');

    expect($result->isOk())->toBeFalse()
        ->and($result->isErr())->toBeTrue();
});

test('成功値と失敗理由は排他的である', function () {
    $ok = Result::ok('value');
    $err = Result::err('reason');

    expect($ok->get())->toBe('value')
        ->and($err->error())->toBe('reason')
        ->and($ok->error())->toBeNull();

    expect(fn () => $err->get())->toThrow(ResultUnwrapException::class);
});

test('ok は明示的なログ collection を受け取れる', function () {
    $entry = new class implements LogEntry {};
    $logs = collect([$entry]);

    $result = Result::ok('value', $logs);

    expect($result->logs())->toHaveCount(1)
        ->and($result->logs()->first())->toBe($entry);
});

test('err は明示的なログ collection を受け取れる', function () {
    $entry = new class implements LogEntry {};
    $logs = collect([$entry]);

    $result = Result::err('reason', $logs);

    expect($result->logs())->toHaveCount(1);
});

test('ログ省略時の ok は空の collection を保持する', function () {
    $result = Result::ok('value');

    expect($result->logs())->toBeInstanceOf(Collection::class)
        ->and($result->logs()->isEmpty())->toBeTrue();
});

test('ログ省略時の err は空の collection を保持する', function () {
    $result = Result::err('reason');

    expect($result->logs()->isEmpty())->toBeTrue();
});

test('get は成功時に値を返す', function () {
    expect(Result::ok(10)->get())->toBe(10);
});

test('getOr は失敗時に代替値を返す', function () {
    expect(Result::err('reason')->getOr('fallback'))->toBe('fallback');
});

test('getOr は成功時に保持している値を返す', function () {
    expect(Result::ok(7)->getOr(0))->toBe(7);
});

test('失敗時の get は ResultUnwrapException を投げる', function () {
    expect(fn () => Result::err('boom')->get())->toThrow(ResultUnwrapException::class);
});

test('error は例外メッセージに依存せず失敗理由を返す', function () {
    $reason = new class
    {
        public string $code = 'E42';
    };

    $result = Result::err($reason);

    expect($result->error())->toBe($reason)
        ->and($result->error()->code)->toBe('E42');
});

test('map は成功時に値を変換する', function () {
    $result = Result::ok(2)->map(fn (int $n): int => $n * 3);

    expect($result->isOk())->toBeTrue()
        ->and($result->get())->toBe(6);
});

test('map は失敗時にコールバックを呼ばず理由を保持する', function () {
    $called = false;
    $result = Result::err('fail')
        ->map(function (mixed $value) use (&$called): mixed {
            $called = true;

            return $value;
        });

    expect($called)->toBeFalse()
        ->and($result->isErr())->toBeTrue()
        ->and($result->error())->toBe('fail');
});

test('map は失敗時にログを保持する', function () {
    $entry = new class implements LogEntry {};
    $result = Result::err('fail', collect([$entry]))->map(fn (mixed $v) => $v);

    expect($result->logs())->toHaveCount(1)
        ->and($result->logs()->first())->toBe($entry);
});

test('map は成功時にログを維持する', function () {
    $entry = new class implements LogEntry {};

    $result = Result::ok(1, collect([$entry]))->map(fn (int $n): int => $n + 1);

    expect($result->get())->toBe(2)
        ->and($result->logs()->all())->toBe([$entry]);
});

test('bind は成功時に Result を連鎖する', function () {
    $result = Result::ok(1)
        ->bind(fn (int $n) => Result::ok($n + 1));

    expect($result->isOk())->toBeTrue()
        ->and($result->get())->toBe(2);
});

test('bind は失敗時にコールバックを呼ばず短絡する', function () {
    $called = false;

    $result = Result::err('fail')
        ->bind(function (mixed $value) use (&$called) {
            $called = true;

            return Result::ok($value);
        });

    expect($called)->toBeFalse()
        ->and($result->error())->toBe('fail');
});

test('bind はログを左から右へ結合する', function () {
    $left = new class implements LogEntry {};
    $right = new class implements LogEntry {};

    $result = Result::ok(1, collect([$left]))
        ->bind(fn (int $n) => Result::ok($n, collect([$right])));

    expect($result->logs())->toHaveCount(2)
        ->and($result->logs()->all())->toBe([$left, $right]);
});

test('pipeMap は単引数 callable を返す', function () {
    $pipe = Result::pipeMap(fn (int $n): int => $n + 5);
    $result = $pipe(Result::ok(10));

    expect($result->get())->toBe(15);
});

test('pipeBind は単引数 callable を返す', function () {
    $pipe = Result::pipeBind(fn (int $n) => Result::ok($n * 2));
    $result = $pipe(Result::ok(4));

    expect($result->get())->toBe(8);
});

test('パイプ演算子で map と bind を連鎖できる', function () {
    $result = Result::ok(2)
        |> Result::pipeBind(fn (int $n) => Result::ok($n + 1))
        |> Result::pipeMap(fn (int $n): int => $n * 3);

    expect($result->get())->toBe(9);
})->skip(fn (): bool => PHP_VERSION_ID < 80500, 'パイプ演算子には PHP 8.5 以上が必要');

test('doo はすべて成功時に最終的な成功 Result を返す', function () {
    $result = Result::doo(function () {
        yield Result::ok(1);
        yield Result::ok(2);

        return null;
    });

    expect($result->isOk())->toBeTrue()
        ->and($result->get())->toBe(2);
});

test('doo は最初の失敗で以降の評価を中断する', function () {
    $evaluated = 0;

    $result = Result::doo(function () use (&$evaluated) {
        yield Result::ok(1);
        $evaluated++;
        yield Result::err('stop');
        $evaluated++;

        return null;
    });

    expect($result->isErr())->toBeTrue()
        ->and($result->error())->toBe('stop')
        ->and($evaluated)->toBe(1);
});

test('doo は評価順にログを結合する', function () {
    $a = new class implements LogEntry {};
    $b = new class implements LogEntry {};

    $result = Result::doo(function () use ($a, $b) {
        yield Result::ok(1, collect([$a]));
        yield Result::ok(2, collect([$b]));

        return null;
    });

    expect($result->logs()->all())->toBe([$a, $b]);
});

test('doo は失敗時に中断時点までのログを結合する', function () {
    $a = new class implements LogEntry {};
    $b = new class implements LogEntry {};

    $result = Result::doo(function () use ($a, $b) {
        yield Result::ok(1, collect([$a]));
        yield Result::err('fail', collect([$b]));

        return null;
    });

    expect($result->isErr())->toBeTrue()
        ->and($result->logs()->all())->toBe([$a, $b]);
});

test('doo は Result 以外の yield を拒否する', function () {
    expect(fn () => Result::doo(function () {
        yield 42;

        return null;
    }))->toThrow(InvalidArgumentException::class, 'Result');
});

test('返却したログ collection の変更は内部状態に影響しない', function () {
    $result = Result::ok('value', collect([]));
    $logs = $result->logs();
    $logs->push(new class implements LogEntry {});

    expect($result->logs()->isEmpty())->toBeTrue();
});
