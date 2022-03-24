<?php

declare(strict_types=1);

namespace Medas\Test\Functional\Databases\Pdo;

use Medas\Test\Functional\BaseTest;

class TableTest extends BaseTest
{
    public function testFetchRecord(): void
    {
        $table = storage()->store('stored_entities');

        self::assertEquals(1, $table->fetchRecord(['id' => 1])['id']);
    }

    public function testFetchAll(): void
    {
        $table = storage()->store('stored_entities');

        self::assertIsArray($table->fetchAll([]));
    }
}
