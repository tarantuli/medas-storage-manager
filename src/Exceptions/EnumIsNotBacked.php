<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class EnumIsNotBacked extends BaseException
{
    public function __construct(string $enumClassName)
    {
        parent::__construct($enumClassName);
    }

    public function pattern(): string
    {
        return 'enum %s is not a backed enum';
    }
}
