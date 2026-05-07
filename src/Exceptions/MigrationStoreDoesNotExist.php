<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationStoreDoesNotExist extends BaseException
{
    public function pattern(): string
    {
        return 'The migration store does not exist, and it cannot be built';
    }
}
