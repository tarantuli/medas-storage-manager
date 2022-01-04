<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\Test\BaseTest;
use Medas\Test\MockUps\StoredEntity;

class HydratorTest extends BaseTest
{
    public function testHydrateEntity(): void
    {
        $entityManager = $this->entityManager();

        $entity = $entityManager->get(StoredEntity::class, 1);

        self::assertInstanceOf(StoredEntity::class, $entity);
        self::assertEquals(1, $entity->id());
        self::assertNull($entity->createdAt);
    }
}
