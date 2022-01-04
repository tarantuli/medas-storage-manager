<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\Test\BaseTest;
use Medas\Test\MockUps\StoredEntity;

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

        $entity = em()->repository(StoredEntity::class)->findOne(['name' => $newName]);

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
}
