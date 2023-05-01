<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};
use Medas\Core\AsSingleton;
use Medas\StorageManager\Interfaces\Store;

class MigrationsStore implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RootGroup::instance();
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
