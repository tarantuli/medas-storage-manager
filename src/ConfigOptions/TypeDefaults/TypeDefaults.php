<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\TypeDefaults;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup};
use Medas\StorageManager\ConfigOptions\StorageManagerConfigGroup;

#[Service]
readonly class TypeDefaults implements ConfigGroup
{
    public function __construct(
        private StorageManagerConfigGroup $group,
    )
    {
    }

    public function parent(): ConfigGroup|null
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'type-defaults';
    }
}
