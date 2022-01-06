<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Migrations;

use Medas\StorageManager\Databases\Pdo\Queries\Query;
use Medas\StorageManager\Migrations\MigrationBuildManager;
use Medas\Test\BaseTest;

class MigrationBuildTest extends BaseTest
{
    public function testCreateMigration(): void
    {
        $migration = $this->createMigrationClassContent();

        self::assertStringContainsString('class Migrations', $migration);
    }

    private function createMigrationClassContent(): string
    {
        $buildManager = service(MigrationBuildManager::class);
        $directory = realpath(__DIR__ . '/../../MockUps/Migrations');

        return $buildManager->createMigration($directory);
    }

    public function testExecuteMigration(): void
    {
        $newStoreName = 'new_stored_entities';
        $existingStoreName = 'stored_entities';

        // Fetch the store; this object should remain working despite the table being deleted and recreated
        $store = storage()->store($newStoreName);

        // Prepare database by deleting the table if it exists
        storage()->deleteStore($newStoreName);

        // Alter the existing store
        (new Query("alter table $existingStoreName modify column name varchar(255) null"))->execute();

        $migration = $this->createMigrationClassContent();
        $this->executeMigration($migration);

        // The table should exist and be empty
        $record = $store->fetchRecord([]);
        self::assertNull($record);
    }
}
