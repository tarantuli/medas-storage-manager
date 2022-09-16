<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\Filters\{Between, LessThan, MoreThan};
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Table;

class BaseSqlQueryBuilder implements QueryBuilder
{
    private string $query;
    private array $arguments;

    public function __construct(
        private readonly Database $database,
    )
    {
    }

    /** @param Table[] $tables */
    public function select(array $tables, array $filters): Query
    {
        $this->arguments = [];
        $this->query = /** @lang text */
            'SELECT * FROM ';

        foreach ($tables as $table) {
            $this->query .= $this->database->quoteIdentifier($table->name) . ',';
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
                $this->query .= $this->database->quoteIdentifier($value->field) . ' < ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof MoreThan) {
                $this->query .= $this->database->quoteIdentifier($value->field) . ' > ? ' . $separator . ' ';
                $this->arguments[] = $value->value;
            }
            elseif ($value instanceof Between) {
                $this->query .= $this->database->quoteIdentifier($value->field) . 'BETWEEN ? AND ? ' . $separator . ' ';
                $this->arguments[] = $value->lowerValue;
                $this->arguments[] = $value->upperValue;
            }
            else {
                if ($value === null && $separator === 'AND') {
                    $this->query .= $this->database->quoteIdentifier($field) . ' IS NULL ' . $separator . ' ';
                }
                else {
                    $this->query .= $this->database->quoteIdentifier($field) . ' = ? ' . $separator . ' ';
                    $this->arguments[] = $value;
                }
            }
        }

        $this->query = substr($this->query, 0, -2 - strlen($separator));
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
            $this->query .= $this->database->quoteIdentifier($field) . ' = ?, ';
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

        $this->query = 'INSERT INTO ' . $this->database->quoteIdentifier($table->name) . ' SET ';
        $this->appendFields($values);

        return new Query($this->query, $this->arguments, $this->database);
    }

    public function showCreate(Table $table): Query
    {
        return new Query('SHOW CREATE TABLE ' . $this->database->quoteIdentifier($table->name), [], $this->database);
    }

    public function dropTable(string $name): Query
    {
        return new Query('DROP TABLE IF EXISTS ' . $this->database->quoteIdentifier($name), [], $this->database);
    }
}
