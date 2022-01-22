<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigManager\ConfigManager;
use Medas\Console\Commands\BaseConsoleCommand;
use Medas\Console\Commands\ConsoleCommandGroup;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\ConfigOptions\OptionController;
use Medas\StorageManager\ConfigOptions\MigrationDirectory;
use Medas\StorageManager\Migrations\MigrationManager;

#[Service]
class MigrateCommand extends BaseConsoleCommand
{
    public function __construct(
        private CommandGroup     $group,
        private ConfigManager    $configManager,
        private MigrationManager $migrationManager,
        private OptionController $optionController,
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

    public function process(array $arguments)
    {
        $this->migrationManager->migrate(
            $this->configManager->getOptionValue(MigrationDirectory::instance())
        );
    }
}
