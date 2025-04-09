<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

/**
 * A service implementing this interface publishes the directories where entities live that should be stored to make a
 * package work.
 */
interface PackageEntities
{
    public function directories(): array;
}
