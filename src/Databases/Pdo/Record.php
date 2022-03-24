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

    public function patch(array $values): void
    {
        $this->data = array_merge($this->data, $values);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
