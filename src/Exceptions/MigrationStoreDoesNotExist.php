<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationStoreDoesNotExist extends BaseException
{
    public function __construct(array $executedMigrations = [])
    {
        $executedList = $executedMigrations
            ? '  - ' . implode("\n  - ", $executedMigrations)
            : '  none';

        parent::__construct($executedList);
    }

    public function pattern(): string
    {
        return "The migration store does not exist\n\nexecuted migrations:\n%s";
    }
}
