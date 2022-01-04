<?php

declare(strict_types=1);

// This file should be in the global namespace

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\StorageManager;

function storage(string $name = null): Storage
{
    /** @var StorageManager $storageManager */
    static $storageManager;

    if (!isset($storageManager)) {
        $storageManager = sm()->resolve(StorageManager::class);
    }

    return $storageManager->get($name);
}
