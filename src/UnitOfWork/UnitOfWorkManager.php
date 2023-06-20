<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\Interfaces\Store;

#[Service]
class UnitOfWorkManager
{
    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions): void
    {
        foreach ($store->prepareUpdate($updates, $conditions) as $action) {
            $unitOfWork->addAction($action);
        }
    }

    public function queueCreate(UnitOfWork $unitOfWork, Store $store, array $values, \Closure $onComplete = null): void
    {
        foreach ($store->prepareCreate($values) as $action) {
            $action->setOnComplete($onComplete);
            $unitOfWork->addAction($action);
        }
    }

    public function queueDelete(UnitOfWork $unitOfWork, Store $store, array $conditions): void
    {
        foreach ($store->prepareDelete($conditions) as $action) {
            $unitOfWork->addAction($action);
        }
    }

    public function queueCollectionUpdate(UnitOfWork $unitOfWork, Store $store, object $entity, string $name, Collection $type, ManagedCollection $values): void
    {
        foreach ($store->prepareCollectionUpdate($entity, $name, $type, $values) as $action) {
            $unitOfWork->addAction($action);
        }
    }
}
