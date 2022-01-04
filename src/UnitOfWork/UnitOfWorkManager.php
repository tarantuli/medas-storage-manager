<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Store;

#[Service]
class UnitOfWorkManager
{
    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions)
    {
        $unitOfWork->addUpdate(
            $store->prepareUpdate($updates, $conditions)
        );
    }

    public function queueCreate(UnitOfWork $unitOfWork, Store $store, array $values, \Closure $onComplete = null)
    {
        $unitOfWork->addCreate(
            $store->prepareCreate($values)->setOnComplete($onComplete)
        );
    }
}
