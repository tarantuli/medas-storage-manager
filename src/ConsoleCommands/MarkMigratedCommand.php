<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\Console\Commands\{Argument, BaseConsoleCommand, CommandInput, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\StorageManager\Migrations\MigrationManager;

#[Service]
readonly class MarkMigratedCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup     $group,
        private MigrationManager $migrationManager,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'mark-migrated';
    }

    public function description(): string
    {
        return 'Marks a migration file as already executed without running it';
    }

    public function arguments(): array
    {
        return [
            Argument::required('filePath'),
        ];
    }

    public function process(CommandInput $input): void
    {
        $this->migrationManager->markMigrated($input->getArgument('filePath'));
    }
}
