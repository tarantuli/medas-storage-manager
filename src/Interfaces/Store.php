<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\UnitOfWork\ActionCollection;

interface Store
{
    public function name(): string;

    public function storage(): Storage;

    public function fetchRecord(array $filters): StoreRecord|null;

    /** @return StoreRecord[]|null */
    public function fetchAll(array $filters): array|null;

    public function fetchCollectionRecord(object $entity, Property $property): iterable;

    public function prepareCreate(array $values): ActionCollection;

    public function prepareGet(array $filters): ActionCollection;

    public function prepareUpdate(array $updates, array $conditions): ActionCollection;

    public function prepareCollectionUpdate(object $entity, string $name, Collection $type, ManagedCollection $values): ActionCollection;

    public function prepareDelete(array $conditions): ActionCollection;

    public function exists(): bool;
}
