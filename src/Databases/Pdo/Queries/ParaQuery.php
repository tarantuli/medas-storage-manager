<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\Selector\Parameter;
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\StorageManager;

class ParaQuery
{
    public function __construct(
        public string   $query,
        /** @var Parameter[] $parameters */
        public array    $parameters,
        public array    $constants,
        public Database $database,
    )
    {
    }

    public function __serialize(): array
    {
        return [
            $this->query,
            $this->parameters,
            $this->constants,
            service(StorageManager::class)->getName($this->database),
        ];
    }

    public function __unserialize(array $data): void
    {
        [$this->query, $this->parameters, $this->constants, $name] = $data;
        $this->database = storage($name);
    }
}
