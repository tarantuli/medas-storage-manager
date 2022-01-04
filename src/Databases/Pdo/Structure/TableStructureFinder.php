<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Field;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Index;
use Medas\StorageManager\Databases\Pdo\Table;

class TableStructureFinder
{
    public function __construct(private Database $database)
    {
    }

    public function find(Table $table): Blueprint|null
    {
        $blueprint = new Blueprint();
        $createTable = $table->getCreateTable();

        if ($createTable === null) {
            return null;
        }

        $this->findName($createTable, $blueprint);
        $this->findFields($createTable, $blueprint);
        $this->findPrimaryKey($createTable, $blueprint);
        $this->findKeys($createTable, $blueprint);

        return $blueprint;
    }

    private function findName(string $createTable, Blueprint $blueprint): void
    {
        if (!preg_match('/CREATE TABLE `([^`]+)/', $createTable, $match)) {
            return;
        }

        $blueprint->name = $match[1];
    }

    private function findFields(string $createTable, Blueprint $blueprint): void
    {
        if (!preg_match_all('/^ +`([^`]+)` (.+?),?$/m', $createTable, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach ($matches as $match) {
            $definition = $match[2];

            // Strip collation
            $definition = preg_replace('/ COLLATE \w+/', '', $definition);

            $blueprint->addField(new Field($match[1], $definition));
        }
    }

    private function findPrimaryKey(string $createTable, Blueprint $blueprint): void
    {
        if (!preg_match('/PRIMARY KEY \(([^)]+)\)/', $createTable, $match)) {
            return;
        }

        $index = new Index('PRIMARY');
        $index->fields = $blueprint->fields($this->getNames($match[1]));
        $index->isUnique = true;

        $blueprint->addIndex($index);
    }

    private function getNames(string $nameString): array
    {
        $names = explode(',', $nameString);

        return array_map(fn($name) => trim($name, '`'), $names);
    }

    private function findKeys(string $createTable, Blueprint $blueprint): void
    {
        if (!preg_match_all(
            '/(?<isUnique>UNIQUE )?KEY `(?<name>[^`]+)` \((?<fields>[^)]+)\)/',
            $createTable,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        foreach ($matches as $match) {
            $index = new Index($match['name']);
            $index->fields = $blueprint->fields($this->getNames($match['fields']));
            $index->isUnique = isset($match['isUnique']);

            $blueprint->addIndex($index);
        }
    }
}
