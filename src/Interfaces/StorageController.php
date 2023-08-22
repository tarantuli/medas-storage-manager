<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function handles(Storage $storage): bool;

    public function store(string $name, Storage $storage = null): Store;

    public function transaction(Storage $storage = null): Transaction;

    public function lastGeneratedValue(Storage $storage = null): int|null;

    public function serializer(Storage $storage = null): Serializer;

    public function actionBuilders(Storage $storage = null): ActionBuilders;

    public function recordFetchers(Storage $storage = null): RecordFetchers;

    public function migrationBuilder(Storage $storage = null): MigrationBuilder;

    public function hasStore(Store $store, Storage $storage = null): bool;
}
