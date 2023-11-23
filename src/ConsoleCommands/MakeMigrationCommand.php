<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\{Commands\BaseConsoleCommand, Commands\ConsoleCommandGroup, Formats\Color, Text};
use Medas\ConsolePrinter\ConsolePrinter;
use Medas\Core\Attributes\Service;
use Medas\EntityManager\ConfigOptions\EntityDirectories;
use Medas\StorageManager\{ConfigOptions\MigrationDirectory, Migrations\MigrationBuildManager};

#[Service]
readonly class MakeMigrationCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup          $group,
        private ConsolePrinter        $consolePrinter,
        private EntityDirectories     $entityDirectories,
        private MigrationBuildManager $migrationBuildManager,
        private MigrationDirectory    $migrationDirectory,
        private OptionController      $optionController,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'make-migration';
    }

    public function description(): string
    {
        return 'Makes a new migration class file';
    }

    public function process(array $arguments): void
    {
        $filePath = $this->migrationBuildManager->createMigration(
            $this->optionController->getValue($this->entityDirectories),
            $this->optionController->getValue($this->migrationDirectory)
        );

        $this->consolePrinter->printEol();

        $filePath
            ? $this->consolePrinter->print(
                new Text('created migration file '),
                new Text($filePath, Color::LightYellow)
            )
            : $this->consolePrinter->print(new Text('no need to create a migration file', Color::LightGray));
    }
}
