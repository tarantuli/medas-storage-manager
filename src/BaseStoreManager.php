<?php

declare(strict_types=1);

namespace Medas\StorageManager;

abstract readonly class BaseStoreManager
{
    abstract protected function blueprint(): Structure\Blueprint;

    abstract protected function name(): string;

    private Interfaces\Store $store;

    public function __construct(
        private StorageManager $storageManager,
    )
    {
        $storageController = $this->storageManager->controller();
        $store = $storageController->store($this->name());

        if (!$storageController->hasStore($store)) {
            $this->build($storageController, $store);
        }

        $this->store = $store;
    }

    private function build(Interfaces\StorageController $storageController, Interfaces\Store $store): void
    {
        $actions = $storageController->actionBuilders()->createStore()
            ->build($store->storage(), $this->blueprint());

        $storageController->actionExecutor()->executeSet($actions);
    }
}
