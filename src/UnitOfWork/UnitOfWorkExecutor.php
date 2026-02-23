<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\StorageManager;

#[Service]
readonly class UnitOfWorkExecutor
{
    public function __construct(
        private StorageManager $storageManager,
    )
    {
    }

    public function execute(UnitOfWork $unitOfWork): void
    {
        if ($unitOfWork->actions() === []) {
            return;
        }

        foreach ($unitOfWork->storages() as $storage) {
            $this->storageManager->controller($storage)->transaction($storage)->begin();
        }

        try {
            foreach ($unitOfWork->actions() as $action) {
                $this->storageManager->controller($action->storage())->actionExecutor()
                    ->execute($action);
            }
        }
        catch (\Throwable $exception) {
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
