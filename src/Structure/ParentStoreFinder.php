<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{MetaData, MetaDataManager};

#[Service]
class ParentStoreFinder
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
    )
    {
    }

    public function find(MetaData $metaData): ParentStores
    {
        $stores = new ParentStores();
        $stores->add($metaData->className, $metaData->entity->store);

        while ($parent = $metaData->parent) {
            $metaData = $this->metaDataManager->get($parent);
            $stores->add($metaData->className, $metaData->entity->store);
        }

        return $stores;
    }
}
