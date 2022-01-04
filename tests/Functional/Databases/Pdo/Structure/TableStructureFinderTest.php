<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Databases\Pdo\Structure;

use Medas\StorageManager\Databases\Pdo\Structure\TableStructureFinder;
use Medas\Test\BaseTest;

class TableStructureFinderTest extends BaseTest
{
    public function testFindStructure(): void
    {
        $tsf = new TableStructureFinder(storage());
        $structure = $tsf->find(storage()->store('stored_entities'));

        self::assertEquals('stored_entities', $structure->name);
        self::assertEquals('datetime DEFAULT NULL', $structure->fields['createdAt']->definition);
        self::assertEquals('id', $structure->indexes['PRIMARY']->fields[0]->name);
        self::assertEquals('name', $structure->indexes['name']->fields[0]->name);
    }
}
