<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\ServiceManager\Attributes\Service;

#[Service]
class UnitOfWorkExecutor
{
    public function execute(UnitOfWork $unitOfWork): bool
    {
        foreach ($unitOfWork->storages() as $storage) {
            $storage->beginTransaction();
        }

        try {
            foreach ($unitOfWork->actions() as $action) {
                $action->execute();
            }
        }
        catch (\Exception) {
            foreach ($unitOfWork->storages() as $storage) {
                $storage->rollbackTransaction();
            }

            return false;
        }

        foreach ($unitOfWork->storages() as $storage) {
            $storage->commitTransaction();
        }

        return true;
    }
}
