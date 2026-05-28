<?php

namespace App\Support\Exceptions;

use LogicException;

/**
 * 失敗状態の Result から成功値を取得しようとしたときに投げられる。
 */
final class ResultUnwrapException extends LogicException
{
    public function __construct()
    {
        parent::__construct('Cannot unwrap a failed Result. Use error() to obtain the failure reason.');
    }
}
