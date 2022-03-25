<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;
use Medas\StorageManager\Databases\Pdo\Structure\Changes;
use Medas\StorageManager\Databases\Pdo\Table;

interface QueryBuilder
{
    public function create(Table $table, array $values): Query;

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): Query;

    public function update(Table $table, array $updates, array $conditions): Query;

    public function delete(Table $table, array $conditions): Query;

    public function showCreate(Table $table): Query;

    public function createTable(Blueprint $blueprint): Query;

    public function alterTable(Changes $changes): Query;

    public function quote(string $identifier): string;

    public function dropTable(string $name): Query;
}
