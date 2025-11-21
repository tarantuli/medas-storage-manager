<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationException extends BaseException
{
    public function __construct(string $migration, string $error)
    {
        parent::__construct($migration, $error);
    }

    public function pattern(): string
    {
        return 'error when executing migration %s: %s';
    }
}
