<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\Validator
};

#[Service]
readonly class GenerateSequencesStore implements ConfigOption, Validator
{
    public function __construct(
        private StorageManagerConfigGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'generate-sequences-store';
    }

    public function description(): string
    {
        return 'Whether migrations should create the sequences store table; off unless an app uses sequence numbering';
    }

    public function isValid(mixed $value): bool
    {
        return is_bool($value);
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): bool
    {
        return false;
    }
}
