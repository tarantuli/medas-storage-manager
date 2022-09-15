<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Databases\Pdo\Structure;

use Medas\StorageManager\Databases\Pdo\Queries\CreateTableBuilder;
use Medas\StorageManager\Databases\Pdo\Structure\EntityStructureFinder;
use Medas\Test\BaseTest;
use Medas\Test\MockUps\Migrations\StoredEntity;

class CreateTableBuilderTest extends BaseTest
{
    public function testCreateQuery(): void
    {
        $esf = service(EntityStructureFinder::class);
        $structure = $esf->find(StoredEntity::class);
        $query = (new CreateTableBuilder($structure))->create(storage());

        $expected = <<<EXPECTED
CREATE TABLE `stored_entities` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `createdAt` datetime DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `name` (`name`)
)

EXPECTED;

        self::assertEquals($expected, $query->query);
    }
}
