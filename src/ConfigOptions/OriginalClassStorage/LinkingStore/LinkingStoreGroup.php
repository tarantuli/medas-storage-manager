<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\OriginalClassStorage\LinkingStore;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\ConfigGroup;
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\OriginalClassStorageGroup;

#[Service]
readonly class LinkingStoreGroup implements ConfigGroup
{
    public function __construct(
        private OriginalClassStorageGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'linking-store';
    }
}
