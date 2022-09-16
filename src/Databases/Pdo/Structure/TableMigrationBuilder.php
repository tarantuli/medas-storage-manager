<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Databases\Pdo\Queries\AlterTableBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\CreateTableBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\Query;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuilder;
use Medas\StorageManager\StorageManager;

#[Service]
class TableMigrationBuilder implements MigrationBuilder
{
    private Database $database;

    public function __construct(
        private readonly ChangeFinder          $changeFinder,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly StorageManager        $storageManager,
        private readonly TableStructureFinder  $tableStructureFinder,
    )
    {
    }

    public function build(Storage $storage, string $className, MethodDefinition $migrateMethod, MethodDefinition $undoMethod): void
    {
        $this->database = $storage;
        if (null === $query = $this->buildQuery($className)) {
            return;
        }

        $queryClass = Query::class;
        $query = trim($query->query);
        $databaseName = $this->storageManager->getName($this->database);

        $argumentsAndDatabase = $databaseName === 'default'
            ? ''
            : sprintf(', [], storage("%s")', $databaseName);

        $migrateMethod->body .= <<<PHP
            \$unitOfWork->addAction(new \\$queryClass("$query"$argumentsAndDatabase));
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
