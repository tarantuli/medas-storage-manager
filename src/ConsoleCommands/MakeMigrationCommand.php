<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\Console\{Commands\BaseConsoleCommand, Commands\ConsoleCommandGroup, Formats\Color, Text};
use Medas\ConsolePrinter\ConsolePrinter;
use Medas\Core\{Attributes\Service, Interfaces\ImplementorFinder};
use Medas\EntityManager\ConfigOptions\EntityDirectories;
use Medas\StorageManager\{
    ConfigOptions\MigrationDirectory,
    Interfaces\PackageEntities,
    Migrations\MigrationBuildManager,
    Migrations\Settings
};

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
        $settings = new Settings(
            $this->optionController->getValue($this->entityDirectories),
            $this->optionController->getValue($this->migrationDirectory)
        );

        if (in_array('--clean', $arguments)) {
            $settings->ignoreExistingStorage = true;
        }

        foreach (service(ImplementorFinder::class)->find(PackageEntities::class) as $packageEntities) {
            $settings->sourceDirectories = array_merge(
                $settings->sourceDirectories,
                $packageEntities->directories()
            );
        }

        $filePath = $this->migrationBuildManager->createMigration($settings);

        $this->consolePrinter->printEol();

        $filePath
            ? $this->consolePrinter->print(
                new Text('created migration file '),
                new Text($filePath, Color::LightYellow)
            )
            : $this->consolePrinter->print(new Text('no need to create a migration file', Color::LightGray));
    }
}
