<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Entities\ValueFetchers\FetchResult;
use Medas\EntityManager\Entities\ValueFetchers\OriginalClassFetcher as OriginalClassFetcherInterface;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\DefaultStrategy;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\Structure\EntityStructureFinder;

#[Service]
readonly class OriginalClassFetcher implements OriginalClassFetcherInterface
{
    public function __construct(
        private EntityStructureFinder        $entityStructureFinder,

        #[ConfigValue(DefaultStrategy::class)]
        private OriginalClassStorageStrategy $originalClassStorageStrategy,
    )
    {
    }

    public function fetch(MetaData $metaData, mixed $id): FetchResult
    {
        $blueprint = $this->entityStructureFinder->find($metaData->className);

        return new FetchResult(true, $this->originalClassStorageStrategy->getOriginalClass($blueprint, $id));
    }
}
