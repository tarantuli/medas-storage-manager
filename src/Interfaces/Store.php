<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\TracksAddsDeletions;
use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\UnitOfWork\Action;

interface Store
{
    public function name(): string;

    public function storage(): Storage;

    public function fetchRecord(array $filters): StoreRecord|null;

    /** @return StoreRecord[]|null */
    public function fetchAll(array $filters): array|null;

    public function fetchCollectionRecord(object $entity, Property $property): iterable;

    public function prepareCreate(array $values): Action;

    public function prepareGet(array $filters): Action;

    public function prepareUpdate(array $updates, array $conditions): Action;

    public function prepareCollectionUpdate(object $entity, string $name, Collection $type, TracksAddsDeletions $values);

    public function prepareDelete(array $conditions);

    public function exists(): bool;
}
