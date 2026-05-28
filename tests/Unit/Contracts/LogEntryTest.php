<?php

use App\Contracts\LogEntry;

test('LogEntry はメソッドを持たないマーカー interface である', function () {
    $reflection = new ReflectionClass(LogEntry::class);

    expect($reflection->isInterface())->toBeTrue()
        ->and($reflection->getNamespaceName())->toBe('App\Contracts')
        ->and($reflection->getMethods())->toBeEmpty();
});

test('LogEntry はメソッド定義なしで実装できる', function () {
    $entry = new class('arbitrary diagnostic payload') implements LogEntry
    {
        public function __construct(
            public readonly mixed $payload,
        ) {}
    };

    expect($entry)->toBeInstanceOf(LogEntry::class)
        ->and($entry->payload)->toBe('arbitrary diagnostic payload');
});

test('LogEntry は出力形式や永続化を要求しない', function () {
    $minimal = new class implements LogEntry {};
    $structured = new class implements LogEntry
    {
        public string $channel = 'domain';

        public array $context = ['id' => 1];
    };

    expect($minimal)->toBeInstanceOf(LogEntry::class)
        ->and($structured)->toBeInstanceOf(LogEntry::class)
        ->and($structured->channel)->toBe('domain');
});
