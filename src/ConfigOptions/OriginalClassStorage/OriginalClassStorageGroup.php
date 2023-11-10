<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\OriginalClassStorage;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\StorageManager\ConfigOptions\RootGroup;

#[Service]
readonly class OriginalClassStorageGroup implements ConfigGroup
{
    public function __construct(
        private RootGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'original-class-storage';
    }
}
