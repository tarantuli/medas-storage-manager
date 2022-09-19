<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Database;
use Medas\PdoStorage\Table;
use Medas\StorageManagerTest\BaseTest;

class DatabaseManagerTest extends BaseTest
{
    public function testConnect(): void
    {
        self::assertInstanceOf(Database::class, storage());
    }

    public function testGetTable(): void
    {
        $table = storage()->store('database_manager_test');
        self::assertInstanceOf(Table::class, $table);
    }
}
