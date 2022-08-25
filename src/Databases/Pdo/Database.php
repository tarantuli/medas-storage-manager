<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo;

use Medas\ServiceManager\ConfigOptions\ConfigValue;
use Medas\StorageManager\ConfigOptions\PdoDns;
use Medas\StorageManager\ConfigOptions\PdoPassword;
use Medas\StorageManager\ConfigOptions\PdoUsername;
use Medas\StorageManager\Databases\Pdo\Exceptions\DriverNotImplementedException;
use Medas\StorageManager\Databases\Pdo\Exceptions\PdoDatabaseException;
use Medas\StorageManager\Databases\Pdo\Queries\BaseSqlQueryBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\MysqlQueryBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\Query;
use Medas\StorageManager\Databases\Pdo\Queries\QueryBuilder;
use Medas\StorageManager\Databases\Pdo\Queries\SelectQueryBuilder;
use Medas\StorageManager\Databases\Pdo\Structure\TableMigrationBuilder;
use Medas\StorageManager\Databases\Pdo\Structure\TypeHandlerFinder;
use Medas\StorageManager\Entities\SelectorActionBuilder;
use Medas\StorageManager\Entities\TypeSerializerFinder;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Interfaces\StoreRecord;
use Medas\StorageManager\Migrations\MigrationBuilder;

class Database implements Storage
{
    private string $name;
    /** @var Table[] */
    private array $tables = [];
    private QueryBuilder $queryBuilder;
    private \PDO $pdo;
    private \PDOStatement $lastStatement;
    private TableMigrationBuilder $migrationBuilder;
    private TypeHandlerFinder $typeHandlerFinder;
    private SelectQueryBuilder $selectQueryBuilder;

    public function __construct(
        #[ConfigValue(PdoDns::class)] private string      $dns,
        #[ConfigValue(PdoUsername::class)] private string $username,
        #[ConfigValue(PdoPassword::class)] private string $password,
    )
    {
        $this->initializePdo();
        $this->initializeBuilders();
    }

    private function initializePdo(): void
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_PERSISTENT => true,
        ];

        $this->pdo = new \PDO($this->dns, $this->username, $this->password, $options);
    }

    private function initializeBuilders(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $this->queryBuilder = match ($driver) {
            'mysql' => new MysqlQueryBuilder($this),
            'sqlite' => new BaseSqlQueryBuilder($this),
            default => throw new DriverNotImplementedException($driver)
        };

        $this->migrationBuilder = sm()->instantiate(TableMigrationBuilder::class);
        $this->migrationBuilder->setDatabase($this);

        $this->typeHandlerFinder = sm()->resolve(TypeSerializerFinder::class);
        $this->selectQueryBuilder = sm()->resolve(SelectQueryBuilder::class);
    }

    public function queryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function stores(): array
    {
        return $this->tables;
    }

    public function store(string $name): Table
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new Table($this, $name);
        }

        return $this->tables[$name];
    }

    public function deleteStore(string $name): void
    {
        $this->execute($this->queryBuilder->dropTable($name));
    }

    public function execute(Query $query): void
    {
        $this->serializeArguments($query);

        try {
            $this->lastStatement = $this->pdo->prepare($query->query);
            $this->lastStatement->execute($query->arguments);
        }
        catch (\Exception|\Error $e) {
            throw new PdoDatabaseException($e->getMessage(), $query);
        }

        if ($onComplete = $query->onComplete()) {
            $onComplete($this);
        }
    }

    private function serializeArguments(Query $query): void
    {
        foreach ($query->arguments as &$argument) {
            if ($argument instanceof \DateTime) {
                $argument = $argument->format('Y-m-d H:i:s');
            }

            if (is_bool($argument)) {
                $argument = (int) $argument;
            }
        }
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commitTransaction(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->commit();
        }
    }

    public function rollbackTransaction(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function lastGeneratedValue(): int|null
    {
        $id = $this->pdo->lastInsertId();

        return $id === false ? null : (int) $id;
    }

    public function migrationBuilder(): MigrationBuilder
    {
        return $this->migrationBuilder;
    }

    public function quote(string $identifier): string
    {
        return $this->queryBuilder->quote($identifier);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function typeSerializerFinder(): TypeSerializerFinder
    {
        return $this->typeHandlerFinder;
    }

    public function selectorActionBuilder(): SelectorActionBuilder
    {
        return $this->selectQueryBuilder;
    }

    public function fetchRecord(): StoreRecord|null
    {
        $data = $this->lastStatement->fetch();
        return is_array($data) ? new Record($data) : null;
    }

    public function fetchRecords(): array
    {
        $data = $this->lastStatement()->fetchAll(\PDO::FETCH_ASSOC);
        $records = [];

        foreach ($data as $set) {
            $records[] = new Record($set);
        }

        return $records;
    }

    public function lastStatement(): \PDOStatement
    {
        return $this->lastStatement;
    }
}
