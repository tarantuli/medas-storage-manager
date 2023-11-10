<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\OriginalClassStorage;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption, Interfaces\Validator};
use Medas\StorageManager\Inheritance\{LinkingStore, OriginalClassStorageStrategy};

#[Service]
readonly class DefaultStrategy implements ConfigOption, Validator
{
    public function __construct(
        private OriginalClassStorageGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'default-strategy';
    }

    public function description(): string
    {
        return 'The default strategy implementation to use when storing the original class of child entities';
    }

    public function isValid(mixed $value): bool
    {
        return $value instanceof OriginalClassStorageStrategy;
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
