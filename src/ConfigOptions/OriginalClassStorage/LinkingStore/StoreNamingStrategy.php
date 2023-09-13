<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\OriginalClassStorage\LinkingStore;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\StorageManager\Inheritance\{LinkinStore\AppendFixedSuffix, LinkinStore\NamingStrategy};

#[Service]
readonly class StoreNamingStrategy implements ConfigOption
{
    public function __construct(
        private LinkingStoreGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'naming-strategy';
    }

    public function description(): string
    {
        return 'The default strategy implementation to use to create linking store names';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): NamingStrategy
    {
        return service(AppendFixedSuffix::class);
    }
}
