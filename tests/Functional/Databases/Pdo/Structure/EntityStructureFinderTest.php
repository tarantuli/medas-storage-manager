<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Databases\Pdo\Structure;

use Medas\StorageManager\Databases\Pdo\Structure\EntityStructureFinder;
use Medas\Test\BaseTest;
use Medas\Test\MockUps\Migrations\StoredEntity;

class EntityStructureFinderTest extends BaseTest
{
    public function testFindStructure(): void
    {
        $esf = service(EntityStructureFinder::class);
        $structure = $esf->find(StoredEntity::class);

        self::assertEquals('stored_entities', $structure->name);
        self::assertEquals('datetime DEFAULT NULL', $structure->fields['createdAt']->definition);
        self::assertEquals('id', $structure->indexes['PRIMARY']->fields[0]->name);
        self::assertEquals('name', $structure->indexes['name']->fields[0]->name);
    }
}
