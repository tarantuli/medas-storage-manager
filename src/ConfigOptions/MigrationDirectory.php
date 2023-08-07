<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\{ConfigGroup, ConfigOption};

#[Service]
class MigrationDirectory implements ConfigOption
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
        return 'migration-directory';
    }

    public function description(): string
    {
        return 'The directory where migration files reside';
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
