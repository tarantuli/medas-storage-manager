<?php

declare(strict_types=1);

namespace Medas\StorageManager\Shared;

use Medas\StorageManager\Interfaces\{Storage, Store};

/** @template T */
abstract class StoreCollection
{
    /** @return T */
    abstract protected function createStore(Storage $storage, string $storeName): Store;

    private array $stores = [];

    /** @return T */
    public function get(Storage $storage, string $storeName): Store
    {
        $databaseName = $storage->name();

        if (!isset($this->stores[$databaseName])) {
            $this->stores[$databaseName] = [];
        }

        $store = $this->stores[$databaseName][$storeName] ?? null;

        // A storage can be replaced under the same name (StorageManager::add()): its
        // stores are made anew, as a cached one would keep pointing at the old storage,
        // and one unit of work would then hold the same storage twice.
        if ($store === null || $store->storage() !== $storage) {
            $store = $this->stores[$databaseName][$storeName] = $this->createStore($storage, $storeName);
        }

        return $store;
    }

    public function delete(Storage $storage, Store $store): void
    {
        if (isset($this->stores[$storage->name()][$store->name()])) {
            unset($this->stores[$storage->name()][$store->name()]);
        }
    }
}
