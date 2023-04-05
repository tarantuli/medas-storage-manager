<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Table;
use Medas\StorageManagerTest\BaseTestClass;

class PropertyHandlerTest extends BaseTestClass
{
    public function testCreateStorage(): void
    {
        storage()->controller()->deleteStore('entities_with_handler');

        $migration = $this->createMigrationClassContent('PropertyHandlers');

        self::assertStringContainsString('`propertyClass` text not null', $migration);

        $this->executeMigration($migration);

        self::assertInstanceOf(Table::class, storage()->store('entities_with_handler'));
    }
}
