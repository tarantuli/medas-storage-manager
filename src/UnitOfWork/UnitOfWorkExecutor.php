<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Service;

#[Service]
class UnitOfWorkExecutor
{
    public function execute(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->storages() as $storage) {
            $storage->controller()->transaction()->begin();
        }

        try {
            foreach ($unitOfWork->actions() as $action) {
                $action->execute();
            }
        }
        catch (\Exception $exception) {
            foreach ($unitOfWork->storages() as $storage) {
                $storage->controller()->transaction()->rollback();
            }

            throw $exception;
        }

        foreach ($unitOfWork->storages() as $storage) {
            $storage->controller()->transaction()->commit();
        }
    }
}
