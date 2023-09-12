<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\ConfigValue;
use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData;
use Medas\StorageManager\ConfigOptions\DefaultOriginalClassStorageStrategy;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\Structure\EntityStructureFinder;

#[Service]
readonly class OriginalClassFetcher implements \Medas\EntityManager\Entities\OriginalClassFetcher
{
    public function __construct(
        private EntityStructureFinder        $entityStructureFinder,

        #[ConfigValue(DefaultOriginalClassStorageStrategy::class)]
        private OriginalClassStorageStrategy $originalClassStorageStrategy,
    )
    {
    }

    public function fetch(MetaData $metaData, mixed $id): string
    {
        $blueprint = $this->entityStructureFinder->find($metaData->className);

        return $this->originalClassStorageStrategy->getOriginalClass($blueprint, $id);
    }
}
