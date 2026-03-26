<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\StorageManager\{ConfigOptions\MigrationDirectory, Migrations\MigrationManager};

#[Service]
readonly class MigrateCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup       $group,
        private MigrationDirectory $migrationDirectory,
        private MigrationManager   $migrationManager,
        private OptionController   $optionController,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'migrate';
    }

    public function aliases(): array
    {
        return ['migrate'];
    }

    public function description(): string
    {
        return 'Migrates the storages';
    }

    public function process(CommandInput $input): void
    {
        $this->migrationManager->migrate($this->optionController->getValue($this->migrationDirectory));
    }
}
