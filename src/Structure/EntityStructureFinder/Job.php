<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\EntityStructureFinder;

use Medas\EntityManager\MetaData;
use Medas\StorageManager\Structure\Blueprint;

readonly class Job
{
    public function __construct(
        public MetaData  $metaData,
        public Blueprint $blueprint,
    )
    {
    }
}
