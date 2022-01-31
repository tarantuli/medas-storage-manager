<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo;

use Medas\StorageManager\Interfaces\StoreRecord;

class Record implements StoreRecord
{
    public function __construct(private array $data)
    {
    }

    public function get(string $name)
    {
        if (!array_key_exists($name, $this->data)) {
            throw new \Exception('database record does not have a field named ' . $name);
        }

        return $this->data[$name];
    }

    public function data(): array
    {
        return $this->data;
    }
}
