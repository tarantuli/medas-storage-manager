<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Service;
use Medas\StorageManager\Interfaces\Store;

#[Service]
class UnitOfWorkManager
{
    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions): void
    {
        $unitOfWork->addAction(
            $store->prepareUpdate($updates, $conditions)
        );
    }

    public function queueCreate(UnitOfWork $unitOfWork, Store $store, array $values, \Closure $onComplete = null): void
    {
        $unitOfWork->addAction(
            $store->prepareCreate($values)->setOnComplete($onComplete)
        );
    }

    public function queueDelete(UnitOfWork $unitOfWork, Store $store, array $conditions): void
    {
        $unitOfWork->addAction(
            $store->prepareDelete($conditions)
        );
    }
}
