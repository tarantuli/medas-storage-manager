<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Attributes\Service;

#[Service]
class UnitOfWorkExecutor
{
    public function execute(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->storages() as $storage) {
            $storage->transaction()->begin();
        }

        try {
            foreach ($unitOfWork->actions() as $action) {
                $action->execute();
            }
        }
        catch (\Exception $exception) {
            foreach ($unitOfWork->storages() as $storage) {
                $storage->transaction()->rollback();
            }

            throw $exception;
        }

        foreach ($unitOfWork->storages() as $storage) {
            $storage->transaction()->commit();
        }
    }
}
