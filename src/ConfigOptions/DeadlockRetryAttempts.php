<?php

declare(strict_types=1);

namespace Medas\StorageManager\ConfigOptions;

use Medas\Core\{
    Attributes\Service,
    Interfaces\ConfigGroup,
    Interfaces\ConfigOption,
    Interfaces\Validator
};

/**
 * How many times to retry a unit of work when a deadlock is detected.
 * Set to 0 to disable automatic retry.
 */
#[Service]
readonly class DeadlockRetryAttempts implements ConfigOption, Validator
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
        return 'deadlock-retry-attempts';
    }

    public function description(): string
    {
        return 'Number of times to automatically retry a transaction when a deadlock is detected (0 = disabled)';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 3;
    }

    public function isValid(mixed $value): bool
    {
        return is_int($value) && $value >= 0;
    }
}
