<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions\OriginalClassStorage\LinkingStore;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\ServiceManager,
    Interfaces\Validator
};
use Medas\StorageManager\Inheritance\LinkingStore\{AppendFixedSuffix, NamingStrategy};

#[Service]
readonly class StoreNamingStrategy implements ConfigOption, Validator
{
    public function __construct(
        private LinkingStoreGroup $group,
        private ServiceManager    $serviceManager,
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

    public function isValid(mixed $value): bool
    {
        return $value instanceof NamingStrategy;
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): NamingStrategy
    {
        return $this->serviceManager->resolve(AppendFixedSuffix::class);
    }
}
