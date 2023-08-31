<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

use Medas\StorageManager\Interfaces\{Storage, Store};

/** @template T */
abstract class StoreCollection
{
    /** @return T */
    abstract protected function createStore($storage, $storeName): Store;

    private array $stores = [];

    /** @return T */
    public function get(Storage $storage, string $storeName): Store
    {
        $databaseName = $storage->name();

        if (!isset($this->stores[$databaseName])) {
            $this->stores[$databaseName] = [];
        }

        if (!isset($this->stores[$databaseName][$storeName])) {
            $this->stores[$databaseName][$storeName] = $this->createStore($storage, $storeName);
        }

        return $this->stores[$databaseName][$storeName];
    }
}
