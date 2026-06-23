<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

class Job
{
    /** @var Migration[]  */
    public array $executedMigrations = [];

    public function __construct(
        public string $directory,

        /** @var string[] */
        public array  $alreadyExecutedMigrations,
    )
    {
    }
}
