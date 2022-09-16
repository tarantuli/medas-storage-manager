<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo;

use Medas\ServiceManager\ConfigOptions\ConfigValue;
use Medas\StorageManager\ConfigOptions\{PdoDns, PdoPassword, PdoUsername};
use Medas\StorageManager\Databases\Pdo\Structure\IdentifierQuoters\IdentifierQuoter;
use Medas\StorageManager\Interfaces\{Storage, StorageController};

class Database implements Storage
{
    private DatabaseController $controller;
    private \PDO $pdo;

    private Queries\QueryBuilder $queryBuilder;
    private IdentifierQuoter $identifierQuoter;

    /** @var Table[] */
    private array $tables = [];

    public function __construct(
        #[ConfigValue(PdoDns::class)] private readonly string      $dns,
        #[ConfigValue(PdoUsername::class)] private readonly string $username,
        #[ConfigValue(PdoPassword::class)] private readonly string $password,
    )
    {
        $this->initializePdo();
        $this->processDriver();

        $this->controller = sm()->resolve(DatabaseController::class);
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

    private function processDriver(): void
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $this->queryBuilder = match ($driver) {
            'mysql' => new Queries\MysqlQueryBuilder($this),
            'sqlite' => new Queries\BaseSqlQueryBuilder($this),
            default => throw new Exceptions\DriverNotImplementedException($driver),
        };

        $this->identifierQuoter = match ($driver) {
            'mysql' => new Structure\IdentifierQuoters\MysqlQuoter(),
            'sqlite' => new Structure\IdentifierQuoters\BaseSqlQuoter(),
            default => throw new Exceptions\DriverNotImplementedException($driver),
        };
    }

    public function controller(): StorageController
    {
        return $this->controller;
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

    public function quoteIdentifier(string $identifier): string
    {
        return $this->identifierQuoter->quote($identifier);
    }

    public function escapeValue(mixed $value): string
    {
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_null($value)) {
            return 'NULL';
        }

        return (string) $this->pdo->quote($value);
    }

    public function execute(Queries\Query $query): void
    {
        $this->controller->execute($this, $this->pdo, $query);
    }

    public function queryBuilder(): Queries\QueryBuilder
    {
        return $this->queryBuilder;
    }
}
