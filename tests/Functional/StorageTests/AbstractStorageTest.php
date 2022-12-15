<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\StorageTests;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManagerTest\BaseTest;
use Medas\StorageManagerTest\MockUps\Relations\Group;
use Medas\StorageManagerTest\MockUps\Relations\Person;

abstract class AbstractStorageTest extends BaseTest
{
    protected Storage $storage;

    public function testPrepare(): void
    {
        em()->clear();
        $this->initialize();

        self::assertInstanceOf(Storage::class, storage());
    }

    /**
     * This method should register a default storage
     */
    abstract protected function initialize(): void;

    /**
     * @depends testPrepare
     */
    public function testMigration(): void
    {
        // Delete both stores if they still exist
        storage()->controller()->deleteStore('other_people');
        storage()->controller()->deleteStore('people');
        storage()->controller()->deleteStore('groups');

        // Create and execute a migration
        $migration = $this->createMigrationClassContent('Relations');

        self::assertStringContainsString('public function migrate(', $migration);
        $this->migrationAssertions($migration);

        $this->executeMigration($migration);

        self::assertTrue(storage()->store('groups')->exists());
        self::assertTrue(storage()->store('people')->exists());
        self::assertTrue(storage()->store('other_people')->exists());
    }

    /**
     * This method should make driver specific assertions about the migration file content,
     * if necessary
     */
    protected function migrationAssertions(string $migration): void
    {
        // Do nothing by default
    }

    /**
     * @depends testMigration
     */
    public function testCreateRecords(): void
    {
        $query = storage()->store('groups')->prepareCreate(['id' => 1, 'name' => 'Test group']);
        $query->execute();
        self::assertFalse($query->recordSet()->hasRecords());

        $query = storage()->store('people')->prepareCreate(['id' => 1, 'name' => 'Test person', 'group' => 1]);
        $query->execute();
        self::assertFalse($query->recordSet()->hasRecords());
    }

    /**
     * @dpeends testCreateRecords
     */
    public function testFetchRecords(): void
    {
        $record = storage()->store('groups')->fetchRecord(['id' => 1]);

        self::assertArrayHasKey('name', $record);
        self::assertEquals('Test group', $record['name']);
    }

    /**
     * @depends testMigration
     */
    public function testCreateGroup(): Group
    {
        $group = em()->create(Group::class, ['name' => 'related group']);
        em()->persist($group);
        em()->flush();

        self::assertIsNumeric($group->id());

        return $group;
    }

    /** @depends testCreateGroup */
    public function testCreatePerson(Group $group): Person
    {
        $person = em()->create(Person::class, ['name' => 'related person', 'group' => $group]);
        em()->persist($person);
        em()->flush();

        self::assertIsNumeric($person->id());

        return $person;
    }

    /**
     * @depends testCreatePerson
     */
    public function testFetchRelation(Person $person): void
    {
        self::assertInstanceOf(Group::class, em()->get(Person::class, $person->id())->group());
    }
}
