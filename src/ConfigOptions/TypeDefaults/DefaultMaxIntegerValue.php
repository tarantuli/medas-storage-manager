<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\TypeDefaults;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\EntityManager\Types\Integer;

#[Service]
readonly class DefaultMaxIntegerValue implements ConfigOption
{

    public function __construct(
        private TypeDefaults $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'default-max-integer-value';
    }

    public function description(): string
    {
        return 'The maximum value an integer field must store if no explicit value is given';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): float
    {
        return Integer::UNSIGNED_8_BYTE_MAX;
    }
}
