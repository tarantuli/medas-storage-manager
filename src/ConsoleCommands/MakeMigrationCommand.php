<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigManager\ConfigManager;
use Medas\Console\Commands\BaseConsoleCommand;
use Medas\Console\Commands\ConsoleCommandGroup;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\OptionController;
use Medas\StorageManager\ConfigOptions\EntityDirectory;
use Medas\StorageManager\ConfigOptions\MigrationDirectory;
use Medas\StorageManager\Migrations\MigrationBuildManager;

#[Service]
class MakeMigrationCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup          $group,
        private ConfigManager         $configManager,
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

    public function process(array $arguments)
    {
        $this->migrationBuildManager->createMigration(
            $this->configManager->getOptionValue(EntityDirectory::instance()),
            $this->configManager->getOptionValue(MigrationDirectory::instance())
        );
    }
}
