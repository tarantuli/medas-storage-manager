<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    ConfigOptions\DispatchDebugInformation,
    Events\DebugInformation,
    Interfaces\ManagedCollection,
    Types\Collection
};
use Medas\StorageManager\{Interfaces\Store, StorageManager};

#[Service]
readonly class UnitOfWorkManager
{
    public function __construct(
        private StorageManager $storageManager,

        #[ConfigValue(DispatchDebugInformation::class)]
        private bool           $dispatchDebugInformation = false,
    )
    {
    }

    public function queueUpdate(UnitOfWork $unitOfWork, Store $store, array $updates, array $conditions): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->update()
            ->build($store, $updates, $conditions);

        foreach ($actions as $action) {
            if ($this->dispatchDebugInformation) {
                dispatch(new DebugInformation('[unit of work manager] update action: %s', $action));
            }

            $unitOfWork->addAction($action);
        }
    }

    public function queueCreate(
        UnitOfWork    $unitOfWork,
        Store         $store,
        array         $values,
        \Closure|null $onComplete = null,
        Priority|null $priority = null,
    ): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->insert()
            ->build($store, $values);

        foreach ($actions as $action) {
            if ($priority) {
                $action->setPriority($priority);
            }

            $action->setOnComplete($onComplete);

            if ($this->dispatchDebugInformation) {
                dispatch(new DebugInformation('[unit of work manager] create action: %s', $action));
            }

            $unitOfWork->addAction($action);
        }
    }

    public function queueDelete(UnitOfWork $unitOfWork, Store $store, array $conditions): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->delete()
            ->build($store, $conditions);

        foreach ($actions as $action) {
            if ($this->dispatchDebugInformation) {
                dispatch(new DebugInformation('[unit of work manager] delete action: %s', $action));
            }

            $unitOfWork->addAction($action);
        }
    }

    public function queueCollectionUpdate(
        UnitOfWork        $unitOfWork,
        Store             $store,
        object            $entity,
        string            $name,
        Collection        $type,
        ManagedCollection $values
    ): void
    {
        $actions = $this->storageManager->controller($store->storage())->actionBuilders()->collectionUpdate()
            ->build($store, $entity, $name, $type, $values);

        foreach ($actions as $action) {
            if ($this->dispatchDebugInformation) {
                dispatch(new DebugInformation('[unit of work manager] collection update action: %s', $action));
            }

            $unitOfWork->addAction($action);
        }
    }
}
