<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;

class CreateTableBuilder
{
    private string $query;

    public function __construct(
        private Blueprint $blueprint,
    )
    {
    }

    public function create(Database $database): Query
    {
        $this->query = sprintf("CREATE TABLE %s (\n", $database->quote($this->blueprint->name));

        $this->addFields($database);
        $this->addKeys($database);

        $this->query = substr($this->query, 0, -2);
        $this->query .= "\n)\n";

        return new Query($this->query, [], $database);
    }

    private function addFields(Database $database): void
    {
        foreach ($this->blueprint->fields as $field) {
            $this->query .= sprintf(" %s %s,\n", $database->quote($field->name), $field->definition);
        }
    }

    private function addKeys(Database $database)
    {
        foreach ($this->blueprint->indexes as $index) {
            if ($index->name === 'PRIMARY') {
                $this->query .= " PRIMARY KEY (";
            }
            else {
                if ($index->isUnique) {
                    $this->query .= " UNIQUE";
                }
                $this->query .= " KEY " . $database->quote($index->name) . ' (';
            }

            foreach ($index->fields as $field) {
                $this->query .= $database->quote($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }
}
