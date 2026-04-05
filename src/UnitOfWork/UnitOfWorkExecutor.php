<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Exceptions\StorageExceptionType,
    Interfaces\StorageException
};
use Medas\StorageManager\{ConfigOptions\DeadlockRetryAttempts, StorageManager};

#[Service]
readonly class UnitOfWorkExecutor
{
    public function __construct(
        private StorageManager $storageManager,

        #[ConfigValue(DeadlockRetryAttempts::class)]
        private int            $deadlockRetryAttempts,
    )
    {
    }

    public function execute(UnitOfWork $unitOfWork): void
    {
        if ($unitOfWork->actions() === []) {
            return;
        }

        $attempts = 0;
        $maxAttempts = 1 + $this->deadlockRetryAttempts;

        while (true) {
            $attempts++;

            try {
                $this->executeOnce($unitOfWork);

                return;
            }
            catch (\Throwable $e) {
                $isDeadlock = $e instanceof StorageException
                    && $e->exceptionType === StorageExceptionType::DeadlockDetected;

                if (!$isDeadlock || $attempts >= $maxAttempts) {
                    throw $e;
                }

                // The storage rolled back the entire transaction on its side — we can safely
                // retry the whole unit of work. Add a small random back-off so that two
                // competing transactions don't deadlock again immediately.
                usleep(random_int(5_000, 50_000));
            }
        }
    }

    private function executeOnce(UnitOfWork $unitOfWork): void
    {
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
