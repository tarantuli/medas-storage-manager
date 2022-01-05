<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Migrations;

use Medas\StorageManager\Databases\Pdo\Queries\Query;
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
        $existingStoreName = 'stored_entities';
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
        $fileName = $directory . DIRECTORY_SEPARATOR . 'migration.php';

        // Fetch the store; this object should remain working despite the table being deleted and recreated
        $store = storage()->store($newStoreName);

        // Prepare database by deleting the table if it exists
        storage()->deleteStore($newStoreName);
        // Alter the existing store
        (new Query("alter table $existingStoreName modify column name varchar(255) null"))->execute();

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

        // Remove the test directory
        unlink($fileName);
        rmdir($directory);
    }
}
