<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class UnhandledTypeString extends BaseException
{
    public function __construct(string $type)
    {
        parent::__construct($type);
    }

    public function pattern(): string
    {
        return 'unhandled type string %s';
    }
}
