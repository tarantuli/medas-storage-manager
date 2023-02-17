<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\Migrations;

use Medas\PdoStorage\Queries\Query;
use Medas\StorageManagerTest\BaseTestClass;

class MigrationBuildTest extends BaseTestClass
{
    public function testExecuteMigration(): void
    {
        $newStoreName = 'new_stored_entities';
        $existingStoreName = 'stored_entities';

        // Fetch the store; this object should remain working despite the table being deleted and recreated
        $store = storage()->store($newStoreName);

        // Prepare database by deleting the table if it exists
        storage()->controller()->deleteStore($newStoreName);

        // Alter the existing store
        (new Query("alter table $existingStoreName modify column name varchar(255) null"))->execute();

        $migration = $this->createMigrationClassContent('Migrations');
        $this->executeMigration($migration);

        // The table should exist and be empty
        $record = $store->fetchRecord([]);
        self::assertNull($record);
    }
}
