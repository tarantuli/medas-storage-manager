<?php

declare(strict_types=1);

namespace Medas\Test\Functional\ConsoleCommands;

use Medas\ConfigOptions\OptionController;
use Medas\StorageManager\ConfigOptions\MigrationDirectory;
use Medas\StorageManager\ConsoleCommands\MakeMigrationCommand;
use Medas\StorageManager\ConsoleCommands\MigrateCommand;
use Medas\StorageManager\Migrations\MigrationManager;
use PHPUnit\Framework\TestCase;

class ConsoleCommandsTest extends TestCase
{
    public function testMakeMigrationCommand(): void
    {
        $directory = $this->getDirectory();
        $initialCount = count(glob($directory . '/*'));

        $this->makeMigration();

        self::assertCount($initialCount + 1, glob($directory . '/*'));

        $this->cleanUp();
    }

    private function getDirectory(): string
    {
        return service(OptionController::class)->getValue(MigrationDirectory::instance());
    }

    private function makeMigration(): void
    {
        service(MakeMigrationCommand::class)->process([]);
    }

    private function cleanUp(): void
    {
        foreach (glob($this->getDirectory() . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testMigrateCommand(): void
    {
        $this->makeMigration();

        service(MigrateCommand::class)->process([]);

        self::assertCount(1, service(MigrationManager::class)->processedMigrations());
        $this->cleanUp();
    }
}
