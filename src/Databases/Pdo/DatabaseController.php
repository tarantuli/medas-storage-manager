<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Entities\SelectorActionBuilder;
use Medas\StorageManager\Entities\TypeSerializerFinder;
use Medas\StorageManager\Interfaces\StorageController;
use Medas\StorageManager\Migrations\MigrationBuilder;

#[Service]
class DatabaseController implements StorageController
{
    public function __construct(
        private readonly Structure\TableMigrationBuilder $migrationBuilder,
        private readonly Structure\TypeHandlerFinder     $typeHandlerFinder,
        private readonly Queries\SelectQueryBuilder      $selectQueryBuilder,
    )
    {
    }

    public function execute(Database $database, \PDO $pdo, Queries\Query $query): void
    {
        $this->serializeArguments($query);

        try {
            $statement = $pdo->prepare($query->query);
            $statement->execute($query->serializedArguments);

            $query->setStatement(new Statement($statement));
        }
        catch (\Exception|\Error $e) {
            throw new Exceptions\PdoDatabaseException($e->getMessage(), $query);
        }

        if ($onComplete = $query->onComplete()) {
            $onComplete($database);
        }
    }

    private function serializeArguments(Queries\Query $query): void
    {
        foreach ($query->arguments as $argument) {
            if ($argument instanceof \DateTime) {
                $argument = $argument->format('Y-m-d H:i:s');
            }

            if (is_bool($argument)) {
                $argument = (int) $argument;
            }

            $query->serializedArguments[] = $argument;
        }
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->migrationBuilder;
    }

    public function typeSerializerFinder(): TypeSerializerFinder
    {
        return $this->typeHandlerFinder;
    }

    public function selectorActionBuilder(): SelectorActionBuilder
    {
        return $this->selectQueryBuilder;
    }
}
