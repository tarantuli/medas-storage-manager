<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class EnumIsNotBackedException extends BaseException
{
    public function __construct(string $enum)
    {
        parent::__construct($enum);
    }

    public function pattern(): string
    {
        return 'enum %s is not a backed enum';
    }
}
