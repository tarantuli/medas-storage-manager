<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\EntityManager\Repository;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\{Migrations\StoredEntity,
    Selectors\StoredEntityWithId,
    Selectors\StoredEntityWithName
};

class EntityPersisterTest extends BaseTestClass
{
    public function testCreateAndFetch(): void
    {
        $entity = new StoredEntity();
        $entity->name = $newName = (string) mt_rand();
        self::assertNull($entity->id());

        em()->persist($entity);
        em()->flush();

        // Clear the cache, fetch the entity again
        em()->clear();

        $entity = service(Repository::class)->fetchOne(
            StoredEntityWithName::instance(),
            ['name' => $newName]
        );

        self::assertIsInt($entity->id());
        self::assertEquals($newName, $entity->name);
    }

    public function testCreateIsIdFilled(): void
    {
        $entity = new StoredEntity();
        $entity->name = $newName = (string) mt_rand();
        self::assertNull($entity->id());

        em()->persist($entity);
        em()->flush();

        // No clearing, no re-fetching
        self::assertIsInt($entity->id());
        self::assertEquals($newName, $entity->name);
    }

    public function testUpdate(): void
    {
        $entity = em()->get(StoredEntity::class, 1);
        $entity->name = $newName = (string) mt_rand();

        em()->flush();
        em()->clear();

        $entity = em()->get(StoredEntity::class, 1);

        self::assertEquals($newName, $entity->name);
    }

    public function testDelete(): void
    {
        // Create and persist a new entity
        $storedEntity = em()->create(StoredEntity::class, ['name' => (string) mt_rand()]);

        em()->persist($storedEntity);
        em()->flush();
        $id = $storedEntity->id();

        // Clear the cache and fetch it from storage, to ensure it was stored
        em()->clear();

        $fetchedEntity = em()->get(StoredEntity::class, $id);
        self::assertInstanceOf(StoredEntity::class, $fetchedEntity);

        // Delete the entity and flush
        em()->delete($fetchedEntity);
        em()->flush();

        // Ensure it does not exist in the cache anymore
        $fetchedEntity = em()->get(StoredEntity::class, $id);
        self::assertFalse(isset($fetchedEntity->name));

        // Clear the cache and fetch it again, to ensure it does not exist in storage anymore
        em()->clear();
        $fetchedEntity = service(Repository::class)->fetchOne(StoredEntityWithId::instance(), ['id' => $id]);
        self::assertNull($fetchedEntity);
    }

    public function testCreateAndFetchWithValues(): void
    {
        $entity = new StoredEntity();
        $entity->name = $newName = (string) mt_rand();
        self::assertNull($entity->id());

        em()->persist($entity);
        em()->flush();

        // Clear the cache, fetch the entity again
        em()->clear();
        $fetchedEntity = service(Repository::class)->getOrCreate(StoredEntity::class, ['name' => $newName]);
        self::assertInstanceOf(StoredEntity::class, $fetchedEntity);
    }
}
