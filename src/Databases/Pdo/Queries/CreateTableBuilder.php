<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;

class CreateTableBuilder
{
    private string $query;
    private Database $database;

    public function __construct(
        private readonly Blueprint $blueprint,
    )
    {
    }

    public function create(Database $database): Query
    {
        $this->database = $database;
        $this->query = sprintf(/** @lang text */ "CREATE TABLE %s (\n", $database->quote($this->blueprint->name));

        $this->addFields();
        $this->addKeys();
        $this->addForeignKeys();

        $this->query = substr($this->query, 0, -2);
        $this->query .= "\n)\n";

        return new Query($this->query, [], $database);
    }

    private function addFields(): void
    {
        foreach ($this->blueprint->fields as $field) {
            $this->query .= sprintf(" %s %s,\n", $this->database->quote($field->name), $field->definition);
        }
    }

    private function addKeys(): void
    {
        foreach ($this->blueprint->indexes as $index) {
            if ($index->name === 'PRIMARY') {
                $this->query .= " PRIMARY KEY (";
            }
            else {
                if ($index->isUnique) {
                    $this->query .= " UNIQUE";
                }
                $this->query .= " KEY " . $this->database->quote($index->name) . ' (';
            }

            foreach ($index->fields as $field) {
                $this->query .= $this->database->quote($field->name) . ',';
            }

            $this->query = substr($this->query, 0, -1) . "),\n";
        }
    }

    private function addForeignKeys(): void
    {
        foreach ($this->blueprint->foreignKeys as $name => $foreignKey) {
            $this->query .= " CONSTRAINT " . $this->database->quote($name) . "\n"
                . "   FOREIGN KEY (" . $this->database->quote($foreignKey->field) . ")\n"
                . "   REFERENCES " . $this->database->quote($foreignKey->foreignEntity)
                . " (" . $this->database->quote($foreignKey->foreignField) . "),\n";
        }
    }
}
