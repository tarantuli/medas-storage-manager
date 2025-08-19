<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{MetaDataManager, Selector\Selector};

#[Service]
readonly class SelectorFetcher
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private StorageManager  $storageManager,
    )
    {
    }

    public function fetch(Selector $selector = null, array $arguments = []): array
    {
        $entity = $selector->entity();
        $metaData = $this->metaDataManager->get($entity);
        $actionSet = $this->storageManager->controller($metaData->entity->storage)->actionBuilders()
            ->selectorAction()->build($selector, $arguments);

        $this->storageManager->controller($metaData->entity->storage)->actionExecutor()->executeSet($actionSet);

        return $actionSet->lastRecordSet->fetchRecords();
    }
}
