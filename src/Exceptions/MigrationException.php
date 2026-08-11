<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationException extends BaseException
{
    public function __construct(string $migration, string $error, array $executedMigrations)
    {
        $executedList = $executedMigrations
            ? '  - ' . implode("\n  - ", array_map(get_class(...), $executedMigrations))
            : '  none';

        parent::__construct($migration, $error, $executedList);
    }

    public function pattern(): string
    {
        return "error when executing migration %s: %s\n\nexecuted migrations:\n%s";
    }
}
