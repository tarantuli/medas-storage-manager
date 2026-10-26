<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

class Job
{
    /** @var Migration[]  */
    public array $executedMigrations = [];

    public function __construct(
        public string        $directory,

        /** @var string[] */
        public array         $alreadyExecutedMigrations,

        // Called with each migration right after it has been executed
        public \Closure|null $onExecuted = null,

        // Tells whether a migration that has not been executed yet should be skipped anyway
        public \Closure|null $skip = null,
    )
    {
    }
}
