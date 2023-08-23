<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\StorageTests;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Interfaces\StorageController;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\PropertyHandlers\EntityWithHandler;
use Medas\StorageManagerTest\MockUps\PropertyHandlers\PropertyClass;
use Medas\StorageManagerTest\MockUps\Relations\Group;
use Medas\StorageManagerTest\MockUps\Relations\Person;

abstract class AbstractStorageTestClass extends BaseTestClass
{
    public function testPrepare(): void
    {
        em()->clear();
        $this->initialize();

        self::assertInstanceOf(Storage::class, $this->storage());
    }

    abstract protected function initialize(): void;

    abstract protected function storage(): Storage;

    abstract protected function controller(): StorageController;

    /**
     * @depends testPrepare
     */
    public function testMigration(): void
    {
        // Delete all stores if they still exist
        $this->controller()->deleteStore($this->controller()->store('r_groups__labels'));
        $this->controller()->deleteStore($this->controller()->store('r_other_people'));
        $this->controller()->deleteStore($this->controller()->store('r_people'));
        $this->controller()->deleteStore($this->controller()->store('r_groups'));
        $this->controller()->deleteStore($this->controller()->store('r_labels'));

        // Create and execute a migration
        $migration = $this->createMigrationClassContent('Relations');

        self::assertStringContainsString('public function migrate(', $migration);
        $this->migrationAssertions($migration);

        $this->executeMigration($migration);

        self::assertTrue($this->controller()->hasStore($this->controller()->store('r_groups')));
        self::assertTrue($this->controller()->hasStore($this->controller()->store('r_people')));
        self::assertTrue($this->controller()->hasStore($this->controller()->store('r_labels')));
        self::assertTrue($this->controller()->hasStore($this->controller()->store('r_other_people')));
        self::assertTrue($this->controller()->hasStore($this->controller()->store('r_groups__labels')));

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
        $action = $this->controller()->actionBuilders()->insert()->build(
            $this->controller()->store('r_groups'),
            ['id' => 1, 'name' => 'Test group']
        );

        $this->controller()->actionExecutor()->executeSet($action);

        self::assertFalse($action->recordSet()->hasRecords());

        $action = $this->controller()->actionBuilders()->insert()->build(
            $this->controller()->store('r_people'),
            ['id' => 1, 'name' => 'Test person', 'group' => 1]
        );

        $this->controller()->actionExecutor()->executeSet($action);

        self::assertFalse($action->recordSet()->hasRecords());
    }

    /**
     * @dpeends testCreateRecords
     */
    public function testFetchRecords(): void
    {
        $record = $this->controller()->recordFetchers()->filteredFetcher()->fetchOne(
            $this->controller()->store('r_groups'),
            ['id' => 1]
        );

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
        $this->controller()->deleteStore($this->controller()->store('entities_with_handler'));

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
