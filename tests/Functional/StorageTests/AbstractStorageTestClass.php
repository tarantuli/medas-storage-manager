<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\StorageTests;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\PropertyHandlers\EntityWithHandler;
use Medas\StorageManagerTest\MockUps\PropertyHandlers\PropertyClass;
use Medas\StorageManagerTest\MockUps\Relations\Group;
use Medas\StorageManagerTest\MockUps\Relations\Person;

abstract class AbstractStorageTestClass extends BaseTestClass
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
        // Delete all stores if they still exist
        storage()->controller()->deleteStore('r_groups__labels');
        storage()->controller()->deleteStore('r_other_people');
        storage()->controller()->deleteStore('r_people');
        storage()->controller()->deleteStore('r_groups');
        storage()->controller()->deleteStore('r_labels');

        // Create and execute a migration
        $migration = $this->createMigrationClassContent('Relations');

        self::assertStringContainsString('public function migrate(', $migration);
        $this->migrationAssertions($migration);

        $this->executeMigration($migration);

        self::assertTrue(storage()->store('r_groups')->exists());
        self::assertTrue(storage()->store('r_people')->exists());
        self::assertTrue(storage()->store('r_labels')->exists());
        self::assertTrue(storage()->store('r_other_people')->exists());
        self::assertTrue(storage()->store('r_groups__labels')->exists());

        // Another migration should be empty
        $migration = $this->createMigrationClassContent('Relations');
        self::assertNull($migration);
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
        $query = storage()->store('r_groups')->prepareCreate(['id' => 1, 'name' => 'Test group']);
        $query->execute();
        self::assertFalse($query->recordSet()->hasRecords());

        $query = storage()->store('r_people')->prepareCreate(['id' => 1, 'name' => 'Test person', 'group' => 1]);
        $query->execute();
        self::assertFalse($query->recordSet()->hasRecords());
    }

    /**
     * @dpeends testCreateRecords
     */
    public function testFetchRecords(): void
    {
        $record = storage()->store('r_groups')->fetchRecord(['id' => 1]);

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

    public function testStoreHandledPRoperty(): void
    {
        storage()->controller()->deleteStore('entities_with_handler');

        // Ensure storage existence
        $migration = $this->createMigrationClassContent('PropertyHandlers');
        $this->executeMigration($migration);

        $entity = em()->create(EntityWithHandler::class, ['propertyClass' => new PropertyClass(1, 10)]);
        em()->persist($entity);
        em()->flush();
        em()->clear();

        // Fetch it again
        $refetchedEntity = em()->get(EntityWithHandler::class, $entity->guid);

        self::assertEquals(1, $refetchedEntity->propertyClass->min);
    }
}
