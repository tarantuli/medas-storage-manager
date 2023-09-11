<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\Structure\EntityStructureFinder;

#[Service]
readonly class OriginalClassFetcher implements \Medas\EntityManager\Entities\OriginalClassFetcher
{
    public function __construct(
        private EntityStructureFinder $entityStructureFinder,
    )
    {
    }

    public function fetch(MetaData $metaData, mixed $id): string
    {
        $blueprint = $this->entityStructureFinder->find($metaData->className);

        return service(OriginalClassStorageStrategy::class)->getOriginalClass($blueprint, $id);
    }
}
