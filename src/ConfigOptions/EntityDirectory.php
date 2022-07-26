<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\ConfigOptions\ConfigGroup;
use Medas\ServiceManager\ConfigOptions\ConfigOption;

class EntityDirectory implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return RootGroup::instance();
    }

    public function name(): string
    {
        return 'entity-directory';
    }

    public function description(): string
    {
        return 'The directory where entity files reside.';
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
        return 'src';
    }
}
