<?php

declare(strict_types=1);

namespace Medas\Test\Functional;

use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Table;
use Medas\Test\BaseTest;

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
