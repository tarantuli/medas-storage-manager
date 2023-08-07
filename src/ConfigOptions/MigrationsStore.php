<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\StorageManager\Interfaces\Store;

#[Service]
class MigrationsStore implements ConfigOption
{
    public function __construct(
        private readonly RootGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'migrations-store';
    }

    public function description(): string
    {
        return 'The store where executed migrations are maintained';
    }

    public function isValid(mixed $value): bool
    {
        return $value instanceof Store;
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): Store
    {
        return storage()->store('medas_migrations');
    }
}
