<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\StorageManager;

#[Service]
class UnitOfWorkExecutor
{
    public function __construct(
        private readonly StorageManager $storageManager,
    )
    {
    }

    public function execute(UnitOfWork $unitOfWork): void
    {
        foreach ($unitOfWork->storages() as $storage) {
            $this->storageManager->controller($storage)->transaction($storage)->begin();
        }

        try {
            foreach ($unitOfWork->actions() as $action) {
                $action->execute();
            }
        }
        catch (\Exception $exception) {
            foreach ($unitOfWork->storages() as $storage) {
                $this->storageManager->controller($storage)->transaction($storage)->rollback();
            }

            throw $exception;
        }

        foreach ($unitOfWork->storages() as $storage) {
            $this->storageManager->controller($storage)->transaction($storage)->commit();
        }
    }
}
