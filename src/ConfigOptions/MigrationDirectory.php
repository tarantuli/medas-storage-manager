<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\ConfigOptions\{ConfigGroup, ConfigOption};

class MigrationDirectory implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RootGroup::instance();
    }

    public function name(): string
    {
        return 'migration-directory';
    }

    public function description(): string
    {
        return 'The directory where migration files reside.';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return 'migrations';
    }
}
