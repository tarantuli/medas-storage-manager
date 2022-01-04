<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Migrations;

use Medas\StorageManager\Migrations\MigrationBuildManager;
use Medas\StorageManager\Migrations\MigrationManager;
use Medas\Test\BaseTest;

class MigrationBuildTest extends BaseTest
{
    public function testCreateMigration(): void
    {
        $migration = $this->createMigrationClassContent();

        self::assertStringContainsString('class Migration', $migration);
    }

    private function createMigrationClassContent(): string
    {
        $buildManager = service(MigrationBuildManager::class);
        $directory = realpath(__DIR__ . '/../../MockUps');

        return $buildManager->createMigration($directory);
    }

    public function testExecuteMigration(): void
    {
        $newStoreName = 'new_stored_entities';
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
        $fileName = $directory . DIRECTORY_SEPARATOR . 'migration.php';

        // Prepare database by deleting the table if it exists
        $store = storage()->store($newStoreName);
        storage()->deleteStore($newStoreName);

        // Prepare the migration test directory
        if (!file_exists($directory)) {
            mkdir($directory);
        }

        // Execute the migration
        $migration = $this->createMigrationClassContent();
        file_put_contents($fileName, $migration);

        $manager = service(MigrationManager::class);
        $manager->migrate($directory);

        // The table should exist and be empty
        $record = $store->fetchRecord([]);
        self::assertNull($record);

        unlink($fileName);
        rmdir($directory);
    }
}
