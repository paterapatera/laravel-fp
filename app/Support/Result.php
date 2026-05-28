<?php

namespace App\Support;

use App\Contracts\LogEntry;
use App\Support\Exceptions\ResultUnwrapException;
use Generator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * @template TValue
 * @template TError
 *
 * @phpstan-immutable
 */
final class Result
{
    /**
     * @param  Collection<int, LogEntry>  $logs
     */
    private function __construct(
        private readonly bool $ok,
        private readonly mixed $value,
        private readonly mixed $error,
        private readonly Collection $logs,
    ) {}

    /**
     * @template T
     *
     * @param  T  $value
     * @param  Collection<int, LogEntry>|null  $logs
     * @return self<T, never>
     */
    public static function ok(mixed $value, ?Collection $logs = null): self
    {
        return new self(true, $value, null, self::normalizeLogs($logs));
    }

    /**
     * @template E
     *
     * @param  E  $reason
     * @param  Collection<int, LogEntry>|null  $logs
     * @return self<never, E>
     */
    public static function err(mixed $reason, ?Collection $logs = null): self
    {
        return new self(false, null, $reason, self::normalizeLogs($logs));
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function isErr(): bool
    {
        return ! $this->ok;
    }

    /**
     * @return TValue
     *
     * @throws ResultUnwrapException
     */
    public function get(): mixed
    {
        if ($this->isErr()) {
            throw new ResultUnwrapException;
        }

        return $this->value;
    }

    /**
     * @param  TValue  $default
     * @return TValue
     */
    public function getOr(mixed $default): mixed
    {
        return $this->isOk() ? $this->value : $default;
    }

    /**
     * @return TError|null
     */
    public function error(): mixed
    {
        return $this->isErr() ? $this->error : null;
    }

    /**
     * @return Collection<int, LogEntry>
     */
    public function logs(): Collection
    {
        return collect($this->logs->all());
    }

    /**
     * @template TNext
     *
     * @param  callable(TValue): TNext  $callback
     * @return self<TNext, TError>
     */
    public function map(callable $callback): self
    {
        if ($this->isErr()) {
            return $this;
        }

        return new self(true, $callback($this->value), null, $this->logs);
    }

    /**
     * @template TValue2
     * @template TError2
     *
     * @param  callable(TValue): self<TValue2, TError2>  $callback
     * @return self<TValue2, TError2>|self<TValue, TError>
     */
    public function bind(callable $callback): self
    {
        if ($this->isErr()) {
            return $this;
        }

        return $this->mergeWith($callback($this->value));
    }

    /**
     * @template TNext
     *
     * @param  callable(TValue): TNext  $callback
     * @return callable(self<TValue, TError>): self<TNext, TError>
     */
    public static function pMap(callable $callback): callable
    {
        return fn (self $result): self => $result->map($callback);
    }

    /**
     * @template TValue2
     * @template TError2
     *
     * @param  callable(TValue): self<TValue2, TError2>  $callback
     * @return callable(self<TValue, TError>): self<TValue2, TError2>
     */
    public static function pBind(callable $callback): callable
    {
        return fn (self $result): self => $result->bind($callback);
    }

    /**
     * @template TValue2
     * @template TError2
     *
     * @param  callable(): Generator<int, self<TValue2, TError2>, mixed, mixed>  $callback
     * @return self<TValue2, TError2>
     */
    public static function doo(callable $callback): self
    {
        $generator = $callback();

        /** @phpstan-ignore instanceof.alwaysTrue */
        if (! $generator instanceof Generator) {
            throw new InvalidArgumentException('doo callback must return a Generator that yields Result instances.');
        }

        $accumulatedLogs = collect();
        $lastValue = null;
        $hasYield = false;

        foreach ($generator as $yielded) {
            /** @phpstan-ignore instanceof.alwaysTrue */
            if (! $yielded instanceof self) {
                throw new InvalidArgumentException('doo generator must only yield Result instances.');
            }

            $hasYield = true;
            $accumulatedLogs = $accumulatedLogs->concat($yielded->logs);

            if ($yielded->isErr()) {
                return new self(false, null, $yielded->error, $accumulatedLogs);
            }

            $lastValue = $yielded->value;
        }

        if (! $hasYield) {
            return new self(true, null, null, $accumulatedLogs);
        }

        return new self(true, $lastValue, null, $accumulatedLogs);
    }

    /**
     * @param  Collection<int, LogEntry>|null  $logs
     * @return Collection<int, LogEntry>
     */
    private static function normalizeLogs(?Collection $logs): Collection
    {
        return $logs !== null ? collect($logs->all()) : collect();
    }

    /**
     * @template TValue2
     * @template TError2
     *
     * @param  self<TValue2, TError2>  $other
     * @return self<TValue2, TError2>|self<TValue, TError>
     */
    private function mergeWith(self $other): self
    {
        $mergedLogs = $this->logs->concat($other->logs);

        if ($other->isOk()) {
            return new self(true, $other->value, null, $mergedLogs);
        }

        return new self(false, null, $other->error, $mergedLogs);
    }
}
