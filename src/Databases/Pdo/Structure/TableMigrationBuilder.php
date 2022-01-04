<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Queries\AlterTableBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\CreateTableBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\Query;
use Medas\StorageManager\Migrations\MigrationBuilder;

class TableMigrationBuilder implements MigrationBuilder
{
    private TableStructureFinder $tableStructureFinder;
    private Database $database;

    public function __construct(
        private ChangeFinder          $changeFinder,
        private EntityStructureFinder $entityStructureFinder,
    )
    {
    }

    public function setDatabase(Database $database): void
    {
        $this->database = $database;
        $this->tableStructureFinder = new TableStructureFinder($database);
    }

    public function build(string $className, MethodDefinition $migrateMethod, MethodDefinition $undoMethod): void
    {
        if (null === $query = $this->buildQuery($className)) {
            return;
        }

        $queryClass = Query::class;

        $migrateMethod->body .= <<<PHP
            \$unitOfWork->addAction(new \\$queryClass("$query->query", [], storage("{$this->database->name()}")));
        PHP;

    }

    private function buildQuery(string $className): Query|null
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $existingStructure = $this->tableStructureFinder->find($this->database->store($expectedStructure->name));

        if ($existingStructure === null) {
            return (new CreateTableBuilder($expectedStructure))->create($this->database);
        }
        else {
            $changes = $this->changeFinder->find($expectedStructure, $existingStructure);
            return $changes ? (new AlterTableBuilder($changes))->create($this->database) : null;
        }
    }
}
