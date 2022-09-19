<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\EntityManager\EntityManager;
use Medas\StorageManagerTest\BaseTest;
use Medas\StorageManagerTest\MockUps\Migrations\StoredEntity;
use function service;

class HydratorTest extends BaseTest
{
    public function testHydrateEntity(): void
    {
        $entityManager = service(EntityManager::class);

        $entity = $entityManager->get(StoredEntity::class, 1);

        self::assertInstanceOf(StoredEntity::class, $entity);
        self::assertEquals(1, $entity->id());
        self::assertNull($entity->createdAt);
    }
}
