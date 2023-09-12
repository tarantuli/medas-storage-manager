<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\StorageManager\Inheritance\{LinkingStore, OriginalClassStorageStrategy};

#[Service]
readonly class DefaultOriginalClassStorageStrategy implements ConfigOption
{
    public function __construct(
        private RootGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'default-original-class-storage-strategy';
    }

    public function description(): string
    {
        return 'The default strategy implementation to use when storing the original class of child entities';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): OriginalClassStorageStrategy
    {
        return service(LinkingStore::class);
    }
}
