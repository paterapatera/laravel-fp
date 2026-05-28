<?php

namespace Tests\Unit\Contracts;

use App\Contracts\LogEntry;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LogEntryTest extends TestCase
{
    public function test_log_entry_is_marker_interface_without_methods(): void
    {
        $reflection = new ReflectionClass(LogEntry::class);

        $this->assertTrue($reflection->isInterface());
        $this->assertSame('App\Contracts', $reflection->getNamespaceName());
        $this->assertEmpty($reflection->getMethods());
    }

    public function test_log_entry_can_be_implemented_without_defining_methods(): void
    {
        $entry = new class('arbitrary diagnostic payload') implements LogEntry
        {
            public function __construct(
                public readonly mixed $payload,
            ) {}
        };

        $this->assertInstanceOf(LogEntry::class, $entry);
        $this->assertSame('arbitrary diagnostic payload', $entry->payload);
    }

    public function test_log_entry_does_not_require_output_format_or_persistence(): void
    {
        $minimal = new class implements LogEntry {};
        $structured = new class implements LogEntry
        {
            public string $channel = 'domain';

            public array $context = ['id' => 1];
        };

        $this->assertInstanceOf(LogEntry::class, $minimal);
        $this->assertInstanceOf(LogEntry::class, $structured);
        $this->assertSame('domain', $structured->channel);
    }
}
