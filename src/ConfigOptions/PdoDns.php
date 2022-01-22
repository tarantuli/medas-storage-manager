<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\ConfigOptions\ConfigGroup;
use Medas\ServiceManager\ConfigOptions\ConfigOption;

class PdoDns implements ConfigOption
{
    use AsSingleton;

    public function group(): ConfigGroup
    {
        return PdoGroup::instance();
    }

    public function name(): string
    {
        return 'dns';
    }

    public function description(): string
    {
        return 'The complete dns string to the database';
    }

    public function isValid(mixed $value): bool
    {
        return is_string($value);
    }

    public function default(): mixed
    {
        return null;
    }
}
