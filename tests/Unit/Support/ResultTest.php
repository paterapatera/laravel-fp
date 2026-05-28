<?php

namespace Tests\Unit\Support;

use App\Contracts\LogEntry;
use App\Support\Exceptions\ResultUnwrapException;
use App\Support\Result;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function test_ok_is_success_and_not_failure(): void
    {
        $result = Result::ok(42);

        $this->assertTrue($result->isOk());
        $this->assertFalse($result->isErr());
    }

    public function test_err_is_failure_and_not_success(): void
    {
        $result = Result::err('reason');

        $this->assertFalse($result->isOk());
        $this->assertTrue($result->isErr());
    }

    public function test_ok_and_err_are_mutually_exclusive_for_value_and_reason(): void
    {
        $ok = Result::ok('value');
        $err = Result::err('reason');

        $this->assertSame('value', $ok->get());
        $this->assertSame('reason', $err->error());
        $this->assertNull($ok->error());

        $this->expectException(ResultUnwrapException::class);
        $err->get();
    }

    public function test_ok_accepts_explicit_logs(): void
    {
        $entry = new class implements LogEntry {};
        $logs = collect([$entry]);

        $result = Result::ok('value', $logs);

        $this->assertCount(1, $result->logs());
        $this->assertSame($entry, $result->logs()->first());
    }

    public function test_err_accepts_explicit_logs(): void
    {
        $entry = new class implements LogEntry {};
        $logs = collect([$entry]);

        $result = Result::err('reason', $logs);

        $this->assertCount(1, $result->logs());
    }

    public function test_ok_without_logs_uses_empty_collection(): void
    {
        $result = Result::ok('value');

        $this->assertInstanceOf(Collection::class, $result->logs());
        $this->assertTrue($result->logs()->isEmpty());
    }

    public function test_err_without_logs_uses_empty_collection(): void
    {
        $result = Result::err('reason');

        $this->assertTrue($result->logs()->isEmpty());
    }

    public function test_get_returns_value_on_success(): void
    {
        $this->assertSame(10, Result::ok(10)->get());
    }

    public function test_get_or_returns_default_on_failure(): void
    {
        $result = Result::err('reason');

        $this->assertSame('fallback', $result->getOr('fallback'));
    }

    public function test_get_or_returns_value_on_success(): void
    {
        $this->assertSame(7, Result::ok(7)->getOr(0));
    }

    public function test_get_on_failure_throws_result_unwrap_exception(): void
    {
        $this->expectException(ResultUnwrapException::class);

        Result::err('boom')->get();
    }

    public function test_error_returns_reason_without_using_exception_message(): void
    {
        $reason = new class
        {
            public string $code = 'E42';
        };

        $result = Result::err($reason);

        $this->assertSame($reason, $result->error());
        $this->assertSame('E42', $result->error()->code);
    }

    public function test_map_transforms_success_value(): void
    {
        $result = Result::ok(2)->map(fn (int $n): int => $n * 3);

        $this->assertTrue($result->isOk());
        $this->assertSame(6, $result->get());
    }

    public function test_map_skips_callback_on_failure_and_preserves_reason(): void
    {
        $called = false;
        $result = Result::err('fail')
            ->map(function (mixed $value) use (&$called): mixed {
                $called = true;

                return $value;
            });

        $this->assertFalse($called);
        $this->assertTrue($result->isErr());
        $this->assertSame('fail', $result->error());
    }

    public function test_map_preserves_logs_on_failure(): void
    {
        $entry = new class implements LogEntry {};
        $result = Result::err('fail', collect([$entry]))->map(fn (mixed $v) => $v);

        $this->assertCount(1, $result->logs());
        $this->assertSame($entry, $result->logs()->first());
    }

    public function test_map_retains_logs_on_success(): void
    {
        $entry = new class implements LogEntry {};

        $result = Result::ok(1, collect([$entry]))->map(fn (int $n): int => $n + 1);

        $this->assertSame(2, $result->get());
        $this->assertSame([$entry], $result->logs()->all());
    }

    public function test_bind_chains_success_results(): void
    {
        $result = Result::ok(1)
            ->bind(fn (int $n) => Result::ok($n + 1));

        $this->assertTrue($result->isOk());
        $this->assertSame(2, $result->get());
    }

    public function test_bind_short_circuits_on_failure_without_calling_callback(): void
    {
        $called = false;

        $result = Result::err('fail')
            ->bind(function (mixed $value) use (&$called) {
                $called = true;

                return Result::ok($value);
            });

        $this->assertFalse($called);
        $this->assertSame('fail', $result->error());
    }

    public function test_bind_merges_logs_left_to_right(): void
    {
        $left = new class implements LogEntry {};
        $right = new class implements LogEntry {};

        $result = Result::ok(1, collect([$left]))
            ->bind(fn (int $n) => Result::ok($n, collect([$right])));

        $this->assertCount(2, $result->logs());
        $this->assertSame([$left, $right], $result->logs()->all());
    }

    public function test_pipe_map_returns_unary_callable(): void
    {
        $pipe = Result::pipeMap(fn (int $n): int => $n + 5);
        $result = $pipe(Result::ok(10));

        $this->assertSame(15, $result->get());
    }

    public function test_pipe_bind_returns_unary_callable(): void
    {
        $pipe = Result::pipeBind(fn (int $n) => Result::ok($n * 2));
        $result = $pipe(Result::ok(4));

        $this->assertSame(8, $result->get());
    }

    #[RequiresPhp('8.5')]
    public function test_pipe_operator_chains_map_and_bind(): void
    {
        $result = Result::ok(2)
            |> Result::pipeBind(fn (int $n) => Result::ok($n + 1))
            |> Result::pipeMap(fn (int $n): int => $n * 3);

        $this->assertSame(9, $result->get());
    }

    public function test_doo_returns_final_success_when_all_steps_succeed(): void
    {
        $result = Result::doo(function () {
            yield Result::ok(1);
            yield Result::ok(2);

            return null;
        });

        $this->assertTrue($result->isOk());
        $this->assertSame(2, $result->get());
    }

    public function test_doo_short_circuits_on_first_failure(): void
    {
        $evaluated = 0;

        $result = Result::doo(function () use (&$evaluated) {
            yield Result::ok(1);
            $evaluated++;
            yield Result::err('stop');
            $evaluated++;

            return null;
        });

        $this->assertTrue($result->isErr());
        $this->assertSame('stop', $result->error());
        $this->assertSame(1, $evaluated);
    }

    public function test_doo_merges_logs_in_evaluation_order(): void
    {
        $a = new class implements LogEntry {};
        $b = new class implements LogEntry {};

        $result = Result::doo(function () use ($a, $b) {
            yield Result::ok(1, collect([$a]));
            yield Result::ok(2, collect([$b]));

            return null;
        });

        $this->assertSame([$a, $b], $result->logs()->all());
    }

    public function test_doo_merges_logs_up_to_failure_point(): void
    {
        $a = new class implements LogEntry {};
        $b = new class implements LogEntry {};

        $result = Result::doo(function () use ($a, $b) {
            yield Result::ok(1, collect([$a]));
            yield Result::err('fail', collect([$b]));

            return null;
        });

        $this->assertTrue($result->isErr());
        $this->assertSame([$a, $b], $result->logs()->all());
    }

    public function test_doo_rejects_non_result_yield(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Result');

        Result::doo(function () {
            yield 42;

            return null;
        });
    }

    public function test_logs_returned_collection_does_not_mutate_internal_state(): void
    {
        $result = Result::ok('value', collect([]));
        $logs = $result->logs();
        $logs->push(new class implements LogEntry {});

        $this->assertTrue($result->logs()->isEmpty());
    }
}
