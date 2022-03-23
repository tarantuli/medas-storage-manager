<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\StorageManager\Migrations\MigrationBuildManager;
use Medas\Test\BaseTest;
use Medas\Test\MockUps\Relations\Group;
use Medas\Test\MockUps\Relations\Person;

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
        $this->rebuildTables();

        // Check that both tables exist and are empty
        self::assertNull(storage()->store('persons')->fetchRecord([]));
        self::assertNull(storage()->store('groups')->fetchRecord([]));
    }

    private function rebuildTables(): void
    {
// Delete both stores if they still exist
        storage()->deleteStore('persons');
        storage()->deleteStore('groups');

        // Execute the migration
        $migration = $this->createMigrationClassContent();
        $this->executeMigration($migration);
    }

    public function testStoreRelation(): void
    {
        $this->rebuildTables();

        $group = em()->create(Group::class, ['name' => 'test group']);
        $person = em()->create(Person::class, ['name' => 'test person', 'group' => $group]);

        em()->persist($group);
        em()->persist($person);
        em()->flush();

        self::assertEquals($group, em()->get(Group::class, 1));
        self::assertEquals($person, em()->get(Person::class, 1));
    }
}

