<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\ActionTypes\Create;
use Medas\StorageManager\UnitOfWork\ActionTypes\Update;

#[Service]
class UnitOfWorkManager
{
    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions): void
    {
        $unitOfWork->addAction(
            $store->prepareUpdate($updates, $conditions)->setType(Update::instance())
        );
    }

    public function queueCreate(UnitOfWork $unitOfWork, Store $store, array $values, \Closure $onComplete = null): void
    {
        $unitOfWork->addAction(
            $store->prepareCreate($values)->setOnComplete($onComplete)->setType(Create::instance())
        );
    }

    public function queueDelete(UnitOfWork $unitOfWork, Store $store, array $conditions): void
    {
        $unitOfWork->addAction(
            $store->prepareDelete($conditions)
        );
    }
}
