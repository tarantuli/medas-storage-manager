<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\{MetaData, MetaDataManager};
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\LinkingStore\StoreNamingStrategy;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\StorageManager;

#[Service]
readonly class LinkingStore implements OriginalClassStorageStrategy
{
    public function __construct(
        #[ConfigValue(StoreNamingStrategy::class)]
        private LinkingStore\NamingStrategy $namingStrategy,
        private MetaDataManager             $metaDataManager,
        private StorageManager              $storageManager,
    )
    {
    }

    public function createValuesToStore(MetaData $metaData, object $entity): array
    {
        $sharedParentStore = $this->metaDataManager->get($metaData->inheritance->sharedParentClass)->entity->store;
        $storeName = $this->namingStrategy->determine($sharedParentStore);

        return [$storeName => ['entityClass' => $entity::class]];
    }

    public function getOriginalClass(MetaData $metaData, Storage $storage, mixed $id): string
    {
        $sharedParentStore = $this->metaDataManager->get($metaData->inheritance->sharedParentClass)->entity->store;
        $storageController = $this->storageManager->controller($storage);

        $actions = $storageController->actionBuilders()->get()->build(
            [$storageController->store($this->namingStrategy->determine($sharedParentStore))],
            [$metaData->idProperty->name => $id]
        );

        $storageController->actionExecutor()->executeSet($actions);

        return $actions->lastRecordSet->fetchRecord()['entityClass'];
    }
}
