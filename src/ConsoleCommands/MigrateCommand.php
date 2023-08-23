<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationDirectory;
use Medas\StorageManager\Migrations\MigrationManager;

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

    public function description(): string
    {
        return 'Migrates the storages';
    }

    public function process(array $arguments): void
    {
        $this->migrationManager->migrate(
            $this->optionController->getValue($this->migrationDirectory)
        );
    }
}
