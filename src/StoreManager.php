<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\Attributes\Service;

#[Service]
readonly class StoreManager
{
    public function __construct(
        private StorageManager $storageManager,
    )
    {
    }

    public function fetch(Interfaces\Store $store, array $filters = []): Interfaces\RecordSet|null
    {
        $controller = $this->storageManager->controller($store->storage());
        $selector = $controller->actionBuilders()->get()->build([$store], $filters);

        $controller->actionExecutor()->executeSet($selector);

        return $selector->lastRecordSet;
    }

    public function insert(Interfaces\Store $store, array $values): Interfaces\RecordSet|null
    {
        $controller = $this->storageManager->controller($store->storage());
        $selector = $controller->actionBuilders()->insert()->build($store, $values);

        $controller->actionExecutor()->executeSet($selector);

        return $selector->lastRecordSet;
    }

    public function update(Interfaces\Store $store, array $updates, array $conditions): Interfaces\RecordSet|null
    {
        $controller = $this->storageManager->controller($store->storage());
        $selector = $controller->actionBuilders()->update()->build($store, $updates, $conditions);

        $controller->actionExecutor()->executeSet($selector);

        return $selector->lastRecordSet;
    }

    public function upsert(Interfaces\Store $store, array $updates, array $conditions): Interfaces\RecordSet|null
    {
        if ($this->fetch($store, $conditions)->hasRecords()) {
            return $this->update($store, $updates, $conditions);
        }
        else {
            return $this->insert($store, array_merge($conditions, $updates));
        }
    }

    public function delete(Interfaces\Store $store, array $conditions): Interfaces\RecordSet|null
    {
        $controller = $this->storageManager->controller($store->storage());
        $selector = $controller->actionBuilders()->delete()->build($store, $conditions);

        $controller->actionExecutor()->executeSet($selector);

        return $selector->lastRecordSet;
    }
}
