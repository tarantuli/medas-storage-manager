<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

readonly class ExecutedMigrationEvent
{
    public function __construct(
        public Migration $migration,
    )
    {
    }
}
