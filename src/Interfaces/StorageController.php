<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;
use Medas\EntityManager\MetaData\Property;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function handles(Storage $storage): bool;

    public function store(string $name, Storage $storage = null): Store;

    /** @return Store[] */
    public function stores(Storage $storage = null): array;

    public function transaction(Storage $storage = null): Transaction;

    public function lastGeneratedValue(Storage $storage = null): int|null;

    public function serializer(): Serializer;

    public function actionBuilder(): ActionBuilder;

    public function migrationBuilder(): MigrationBuilder;

    public function fetchRecord(Store $store, array $filters, Storage $storage = null): StoreRecord|null;

    /** @return StoreRecord[]|null */
    public function fetchAll(Store $store, array $filters, Storage $storage = null): array|null;

    public function fetchCollectionRecord(Store $store, object $entity, Property $property, Storage $storage = null): iterable;

    public function hasStore(Store $store, Storage $storage = null): bool;
}
