<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\Filters\{Between, LessThan, MoreThan};
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;
use Medas\StorageManager\Databases\Pdo\Structure\Changes;
use Medas\StorageManager\Databases\Pdo\Table;

class BaseSqlQueryBuilder implements QueryBuilder
{
    private string $query;
    private array $arguments;

    public function __construct(private Database $database)
    {
    }

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): Query
    {
        $this->arguments = [];
        $this->query = 'SELECT * FROM ';

        foreach ($tables as $table) {
            $this->query .= $this->database->quote($table->name) . ',';
        }

        $this->query = substr($this->query, 0, -1);

        if ($filters) {
            $this->query .= ' WHERE ';
            $this->appendConditions($filters);
        }

        return new Query($this->query, $this->arguments, $this->database);
    }

    /** @noinspection PhpSameParameterValueInspection */
    private function appendConditions(array $filters, string $separator = 'AND'): void
    {
        foreach ($filters as $field => $value) {
            if ($value instanceof LessThan) {
                $this->query .= $this->quote($value->field) . ' < ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof MoreThan) {
                $this->query .= $this->quote($value->field) . ' > ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof Between) {
                $this->query .= $this->quote($value->field) . 'BETWEEN ? AND ? ' . $separator . ' ';
                $this->arguments[] = $value->lowerValue;
                $this->arguments[] = $value->upperValue;
            }
            else {
                if ($value === null && $separator === 'AND') {
                    $this->query .= $this->quote($field) . ' IS NULL ' . $separator . ' ';
                }
                else {
                    $this->query .= $this->quote($field) . ' = ? ' . $separator . ' ';
                    $this->arguments[] = $value;
                }
            }
        }

        $this->query = substr($this->query, 0, -2 - strlen($separator));
    }

    public function quote(string $identifier): string
    {
        return '"' . $identifier . '"';
    }

    public function update(Table $table, array $updates, array $conditions): Query
    {
        $this->arguments = [];

        $this->query = 'UPDATE ' . $table->name . ' SET ';
        $this->appendFields($updates);

        $this->query .= ' WHERE ';
        $this->appendConditions($conditions);

        return new Query($this->query, $this->arguments, $this->database);
    }

    private function appendFields(array $fields): void
    {
        foreach ($fields as $field => $value) {
            $this->query .= $this->quote($field) . ' = ?, ';
            $this->arguments[] = $value;
        }

        $this->query = substr($this->query, 0, -2);
    }

    public function delete(Table $table, array $conditions): Query
    {
        $this->arguments = [];

        $this->query = 'DELETE FROM ' . $table->name . ' WHERE ';
        $this->appendConditions($conditions);

        return new Query($this->query, $this->arguments, $this->database);
    }

    public function create(Table $table, array $values): Query
    {
        $this->arguments = [];

        $this->query = 'INSERT INTO ' . $this->database->quote($table->name) . ' SET ';
        $this->appendFields($values);

        return new Query($this->query, $this->arguments, $this->database);
    }

    public function showCreate(Table $table): Query
    {
        return new Query('SHOW CREATE TABLE ' . $this->database->quote($table->name), [], $this->database);
    }

    public function createTable(Blueprint $blueprint): Query
    {
        return (new CreateTableBuilder($blueprint))->create($this->database);
    }

    public function alterTable(Changes $changes): Query
    {
        return (new AlterTableBuilder($changes))->create($this->database);
    }

    public function dropTable(string $name): Query
    {
        return new Query('DROP TABLE IF EXISTS ' . $this->quote($name), [], $this->database);
    }
}
