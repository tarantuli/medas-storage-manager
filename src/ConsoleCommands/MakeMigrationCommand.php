<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\Commands\BaseConsoleCommand;
use Medas\Console\Commands\ConsoleCommandGroup;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\ConfigOptions\EntityDirectory;
use Medas\StorageManager\ConfigOptions\MigrationDirectory;
use Medas\StorageManager\Migrations\MigrationBuildManager;

#[Service]
class MakeMigrationCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup          $group,
        private MigrationBuildManager $migrationBuildManager,
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
        $this->migrationBuildManager->createMigration(
            $this->optionController->getValue(EntityDirectory::instance()),
            $this->optionController->getValue(MigrationDirectory::instance())
        );
    }
}
