<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\StorageManager;

#[Service]
class UnitOfWorkManager
{
    public function __construct(
        private readonly StorageManager $storageManager,
    )
    {
    }

    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->update()
            ->build($store, $updates, $conditions);

        foreach ($actions as $action) {
            $unitOfWork->addAction($action);
        }
    }

    public function queueCreate(
        UnitOfWork $unitOfWork,
        Store      $store,
        array      $values,
        \Closure   $onComplete = null,
        Priority   $priority = null,
    ): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->insert()
            ->build($store, $values);

        foreach ($actions as $action) {
            if ($priority) {
                $action->setPriority($priority);
            }

            $action->setOnComplete($onComplete);

            $unitOfWork->addAction($action);
        }
    }

    public function queueDelete(UnitOfWork $unitOfWork, Store $store, array $conditions): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->delete()
            ->build($store, $conditions);

        foreach ($actions as $action) {
            $unitOfWork->addAction($action);
        }
    }

    public function queueCollectionUpdate(UnitOfWork $unitOfWork, Store $store, object $entity, string $name, Collection $type, ManagedCollection $values): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->collectionUpdate()
            ->build($store, $entity, $name, $type, $values);

        foreach ($actions as $action) {
            $unitOfWork->addAction($action);
        }
    }
}
