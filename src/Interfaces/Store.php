<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\UnitOfWork\Action;

interface Store
{
    public function name(): string;

    public function storage(): Storage;

    public function fetchRecord(array $filters): StoreRecord|null;

    public function fetchAll(array $filters): array|null;

    public function prepareCreate(array $values): Action;

    public function prepareGet(array $filters): Action;

    public function prepareUpdate(array $updates, array $conditions): Action;

    public function prepareDelete(array $conditions);

    public function exists(): bool;
}
