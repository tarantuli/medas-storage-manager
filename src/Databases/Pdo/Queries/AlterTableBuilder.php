<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Structure\Changes;

class AlterTableBuilder
{
    public function __construct(
        private readonly Changes $changes,
    )
    {
    }

    public function create(Database $database): Query
    {
        $query = 'ALTER TABLE ' . $database->quote($this->changes->name) . "\n";

        foreach ($this->changes->addFields as $field) {
            $query .= sprintf("ADD COLUMN %s %s,\n", $database->quote($field->name), $field->definition);
        }

        foreach ($this->changes->changeFields as $field) {
            $query .= sprintf("MODIFY COLUMN %1\$s %2\$s,\n", $database->quote($field->name), $field->definition);
        }

        $query = substr($query, 0, -2);

        return new Query($query, [], $database);
    }
}
