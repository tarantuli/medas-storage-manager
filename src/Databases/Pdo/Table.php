<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo;

use Medas\StorageManager\Databases\Pdo\Exceptions\PdoDatabaseException;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\Action;

class Table implements Store
{
    public function __construct(
        public Database $database,
        public string   $name,
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function storage(): Database
    {
        return $this->database;
    }

    public function fetchRecord(array $filters): Record|null
    {
        $this->database->execute($this->prepareGet($filters));

        $data = $this->database->lastStatement()->fetch();
        return is_array($data) ? new Record($data) : null;
    }

    public function fetchAll(array $filters): array|null
    {
        $this->database->execute($this->prepareGet($filters));

        return $this->database->lastStatement()->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function prepareGet(array $filters): Action
    {
        return $this->database->queryBuilder()->select([$this], $filters);
    }

    public function prepareCreate(array $values): Action
    {
        return $this->database->queryBuilder()->create($this, $values);
    }

    public function prepareUpdate(array $updates, array $conditions): Action
    {
        return $this->database->queryBuilder()->update($this, $updates, $conditions);
    }

    public function getCreateTable(): string|null
    {

        try {
            $this->database->queryBuilder()->showCreate($this)->execute();
            return $this->database->lastStatement()->fetchColumn(1);
        }
            /** @noinspection PhpRedundantCatchClauseInspection */
        catch (PdoDatabaseException) {
            return null;
        }
    }
}
