<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Entities\ValueFetchers\{
    FetchResult,
    OriginalClassFetcher as OriginalClassFetcherInterface
};
use Medas\EntityManager\MetaData;
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\DefaultStrategy;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\StorageManager;

#[Service]
readonly class OriginalClassFetcher implements OriginalClassFetcherInterface
{
    public function __construct(
        #[ConfigValue(DefaultStrategy::class)]
        private OriginalClassStorageStrategy $originalClassStorageStrategy,
        private StorageManager               $storageManager,
    )
    {
    }

    public function fetch(MetaData $metaData, mixed $id): FetchResult
    {
        $storage = $this->storageManager->byName($metaData->entity->storage);

        return new FetchResult(
            true,
            $this->originalClassStorageStrategy->getOriginalClass($metaData, $storage, $id)
        );
    }
}
