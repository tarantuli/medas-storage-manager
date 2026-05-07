<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;

interface StorageController
{
    public function handles(Storage $storage): bool;

    public function serializer(Storage|null $storage = null): Serializer;

    public function transaction(Storage|null $storage = null): Transaction;

    public function lastGeneratedValue(Storage|null $storage = null): int|null;

    public function store(string $name, Storage|null $storage = null): Store;

    public function deleteStore(Store $store): void;

    /** @return Store[] */
    public function getStores(Storage|null $storage = null, string|null $nameFilter = null): array;

    public function hasStore(Store $store, Storage|null $storage = null): bool;

    public function actionBuilders(): ActionBuilders;

    public function actionExecutor(): ActionExecutor;

    public function recordFetchers(): RecordFetchers;
}
