<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\Console\{
    Commands\BaseConsoleCommand,
    Commands\CommandInput,
    Commands\ConsoleCommandGroup,
    Formats\SafeColor,
    Printer,
    Text
};
use Medas\Core\{Attributes\EventListener, Attributes\Service, Interfaces\ConfigOptionController};
use Medas\StorageManager\{
    ConfigOptions\MigrationDirectory,
    Migrations\ExecutedMigrationEvent,
    Migrations\MigrationManager
};

#[Service]
readonly class MigrateCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup           $group,
        private ConfigOptionController $optionController,
        private MigrationDirectory     $migrationDirectory,
        private MigrationManager       $migrationManager,
        private Printer                $consolePrinter,
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

    #[EventListener]
    public function handleExecution(ExecutedMigrationEvent $event): void
    {
        $this->consolePrinter->printLine(
            new Text('executed migration '),
            new Text($event->migration::class, SafeColor::LightYellow)
        );
    }
}
