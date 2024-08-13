<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function handles(Storage $storage): bool;

    public function serializer(Storage $storage = null): Serializer;

    public function transaction(Storage $storage = null): Transaction;

    public function lastGeneratedValue(Storage $storage = null): int|null;

    public function store(string $name, Storage $storage = null): Store;

    public function deleteStore(Store $store): void;

    /** @return Store[] */
    public function getStores(Storage $storage = null, string $nameFilter = null): array;

    public function hasStore(Store $store, Storage $storage = null): bool;

    public function actionBuilders(): ActionBuilders;

    public function actionExecutor(): ActionExecutor;

    public function recordFetchers(): RecordFetchers;

    public function migrationBuilder(): MigrationBuilder;
}
