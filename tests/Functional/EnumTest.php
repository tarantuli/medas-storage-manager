<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Table;
use Medas\StorageManager\Exceptions\EnumIsNotBacked;
use Medas\StorageManagerTest\BaseTestClass;

class EnumTest extends BaseTestClass
{
    public function testUnbackedEnum(): void
    {
        self::expectException(EnumIsNotBacked::class);
        $this->createMigrationClassContent('UnbackedEnums');
    }

    public function testBackedEnum(): void
    {
        storage()->controller()->deleteStore('backed_enum_entities');

        $migration = $this->createMigrationClassContent('BackedEnums');

        self::assertStringContainsString('`enum` tinyint', $migration);
        self::assertStringContainsString('char(3)', $migration);

        $this->executeMigration($migration);

        self::assertInstanceOf(Table::class, storage()->store('backed_enum_entities'));
    }
}
