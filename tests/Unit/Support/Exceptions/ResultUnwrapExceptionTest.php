<?php

namespace Tests\Unit\Support\Exceptions;

use App\Support\Exceptions\ResultUnwrapException;
use LogicException;
use PHPUnit\Framework\TestCase;

class ResultUnwrapExceptionTest extends TestCase
{
    public function test_extends_logic_exception(): void
    {
        $exception = new ResultUnwrapException;

        $this->assertInstanceOf(LogicException::class, $exception);
    }

    public function test_message_indicates_failed_result_unwrap(): void
    {
        $exception = new ResultUnwrapException;

        $this->assertStringContainsString('unwrap', strtolower($exception->getMessage()));
        $this->assertStringContainsString('failed', strtolower($exception->getMessage()));
    }
}
