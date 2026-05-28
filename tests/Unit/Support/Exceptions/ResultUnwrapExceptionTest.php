<?php

use App\Support\Exceptions\ResultUnwrapException;

test('ResultUnwrapException は LogicException を継承する', function () {
    expect(new ResultUnwrapException)->toBeInstanceOf(LogicException::class);
});

test('メッセージは失敗 Result の unwrap 不可を示す', function () {
    $message = strtolower((new ResultUnwrapException)->getMessage());

    expect($message)->toContain('unwrap')
        ->and($message)->toContain('failed');
});
