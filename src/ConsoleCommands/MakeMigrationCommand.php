<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Console\Formats\Color;
use Medas\Console\Text;
use Medas\ConsolePrinter\ConsolePrinter;
use Medas\ServiceManager\Service;
use Medas\StorageManager\ConfigOptions\{EntityDirectory, MigrationDirectory};
use Medas\StorageManager\Migrations\MigrationBuildManager;

#[Service]
class MakeMigrationCommand extends BaseConsoleCommand
{
    public function __construct(
        private readonly CommandGroup          $group,
        private readonly ConsolePrinter        $consolePrinter,
        private readonly MigrationBuildManager $migrationBuildManager,
        private readonly OptionController      $optionController,
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
            $this->optionController->getValue(EntityDirectory::instance()),
            $this->optionController->getValue(MigrationDirectory::instance())
        );

        $this->consolePrinter->printEol();

        $filePath
            ? $this->consolePrinter->print(new Text('created migration file '), new Text($filePath, Color::LightYellow))
            : $this->consolePrinter->print(new Text('no need to create a migration file', Color::LightGray));
    }
}
