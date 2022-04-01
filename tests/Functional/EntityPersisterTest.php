<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\Test\BaseTest;
use Medas\Test\MockUps\Migrations\StoredEntity;
use Medas\Test\MockUps\Selectors\StoredEntityWithId;
use Medas\Test\MockUps\Selectors\StoredEntityWithName;

class EntityPersisterTest extends BaseTest
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

        $entity = em()->repository(StoredEntity::class)->findOne(
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
        var_dump($id);

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

        // Clerar the cache and fetch it again, to ensure it does not exist in storage anymore
        em()->clear();
        $repo = em()->repository(StoredEntity::class);
        $fetchedEntity = $repo->findOne(StoredEntityWithId::instance(), ['id' => $id]);
        self::assertNull($fetchedEntity);
    }
}
