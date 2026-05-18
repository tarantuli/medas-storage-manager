<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationAlreadyExecuted extends BaseException
{
    public function __construct(string $migrationClass)
    {
        parent::__construct($migrationClass);
    }

    public function pattern(): string
    {
        return 'Migration has already been marked as executed: %s';
    }
}
