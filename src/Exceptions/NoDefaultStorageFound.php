<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class NoDefaultStorageFound extends BaseException
{
    public function pattern(): string
    {
        return 'No default storage found';
    }
}
