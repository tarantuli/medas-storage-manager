<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\StorageManager\Migrations\MigrationBuildManager;
use Medas\Test\BaseTest;

class OneToManyRelationTest extends BaseTest
{
    public function testCreateMigration(): void
    {
        $migration = $this->createMigrationClassContent();

        self::assertStringContainsString('class Migration', $migration);
    }

    private function createMigrationClassContent(): string
    {
        $buildManager = service(MigrationBuildManager::class);
        $directory = realpath(__DIR__ . '/../MockUps/Relations');

        return $buildManager->createMigrationClass($directory);
    }

    public function testExecuteMigration(): void
    {
        // Delete both stores if they still exist
        storage()->deleteStore('persons');
        storage()->deleteStore('groups');

        // Execute the migration
        $migration = $this->createMigrationClassContent();
        $this->executeMigration($migration);

        // Check that both tables exist and are empty
        self::assertNull(storage()->store('persons')->fetchRecord([]));
        self::assertNull(storage()->store('groups')->fetchRecord([]));
    }
}

