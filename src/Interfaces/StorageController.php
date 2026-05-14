<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;

interface StorageController
{
    public function initialize(): void;

    public function handles(Storage $storage): bool;

    public function hasStore(Store $store, Storage|null $storage = null): bool;

    public function store(string $name, Storage|null $storage = null): Store;

    /** @return Store[] */
    public function getStores(Storage|null $storage = null, string|null $nameFilter = null): array;

    public function deleteStore(Store $store): void;

    public function transaction(Storage|null $storage = null): Transaction;

    public function serializer(Storage|null $storage = null): Serializer;

    public function actionBuilders(): ActionBuilders;

    public function actionExecutor(): ActionExecutor;

    public function recordFetchers(): RecordFetchers;

    public function lastGeneratedValue(Storage|null $storage = null): int|null;
}
