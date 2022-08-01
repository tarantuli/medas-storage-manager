<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\ConfigOptions\{ConfigGroup};
use Medas\ServiceManager\AsSingleton;

class RootGroup implements ConfigGroup
{
    use AsSingleton;

    public function parent(): ConfigGroup|null
    {
        return null;
    }

    public function name(): string
    {
        return 'storage';
    }
}
