<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Field;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Index;
use Medas\StorageManager\Databases\Pdo\Table;

#[Service]
class TableStructureFinder
{
    private Blueprint $blueprint;
    private string|null $createTable;

    public function find(Table $table): Blueprint|null
    {
        $this->blueprint = new Blueprint();
        $this->createTable = $table->getCreateTable();

        if ($this->createTable === null) {
            return null;
        }

        $this->findName();
        $this->findFields();
        $this->findPrimaryKey();
        $this->findKeys();

        return $this->blueprint;
    }

    private function findName(): void
    {
        if (!preg_match('/CREATE TABLE `([^`]+)/', $this->createTable, $match)) {
            return;
        }

        $this->blueprint->name = $match[1];
    }

    private function findFields(): void
    {
        if (!preg_match_all('/^ +`([^`]+)` (.+?),?$/m', $this->createTable, $matches, PREG_SET_ORDER)) {
            return;
        }
        foreach ($matches as $match) {
            $definition = $match[2];

            // Strip collation
            $definition = preg_replace('/ COLLATE \w+/', '', $definition);

            $this->blueprint->addField(new Field($match[1], $definition));
        }
    }

    private function findPrimaryKey(): void
    {
        if (!preg_match('/PRIMARY KEY \(([^)]+)\)/', $this->createTable, $match)) {
            return;
        }

        $index = new Index('PRIMARY');
        $index->fields = $this->blueprint->fields($this->getNames($match[1]));
        $index->isUnique = true;

        $this->blueprint->addIndex($index);
    }

    private function getNames(string $nameString): array
    {
        $names = explode(',', $nameString);

        return array_map(fn($name) => trim($name, '`'), $names);
    }

    private function findKeys(): void
    {
        if (!preg_match_all(
            '/(?<isUnique>UNIQUE )?KEY `(?<name>[^`]+)` \((?<fields>[^)]+)\)/',
            $this->createTable,
            $matches,
            PREG_SET_ORDER
        )) {
            return;
        }

        foreach ($matches as $match) {
            $index = new Index($match['name']);
            $index->fields = $this->blueprint->fields($this->getNames($match['fields']));
            $index->isUnique = isset($match['isUnique']);

            $this->blueprint->addIndex($index);
        }
    }
}
