<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption, Interfaces\Validator};

#[Service]
readonly class MigrationsStoreName implements ConfigOption, Validator
{
    public function __construct(
        private RootGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'migrations-store-name';
    }

    public function description(): string
    {
        return 'The name of the store where executed migrations are registered';
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
        return '__migrations';
    }
}
