<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\StorageManagerTest\BaseTest;
use Medas\StorageManagerTest\MockUps\Relations\{Group, Person};

class OneToManyRelationTest extends BaseTest
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('other_people');
        storage()->controller()->deleteStore('people');
        storage()->controller()->deleteStore('groups');

        $migration = $this->createMigrationClassContent('Relations');

        self::assertStringContainsString('class Migration', $migration);
    }

    public function testExecuteMigration(): void
    {
        $this->rebuildTables();

        // Check that both tables exist and are empty
        self::assertNull(storage()->store('people')->fetchRecord([]));
        self::assertNull(storage()->store('groups')->fetchRecord([]));
    }

    private function rebuildTables(): void
    {
        // Delete both stores if they still exist
        storage()->controller()->deleteStore('other_people');
        storage()->controller()->deleteStore('people');
        storage()->controller()->deleteStore('groups');

        // Execute the migration
        $migration = $this->createMigrationClassContent('Relations');
        $this->executeMigration($migration);
    }

    public function testStoreRelation(): void
    {
        $this->rebuildTables();

        $group = em()->create(Group::class, ['name' => 'test group']);
        em()->persist($group);
        em()->flush();

        $person = em()->create(Person::class, ['name' => 'test person', 'group' => $group]);
        em()->persist($person);
        em()->flush();

        self::assertEquals($group, em()->get(Group::class, 1));
        self::assertEquals($person, em()->get(Person::class, 1));
    }
}

