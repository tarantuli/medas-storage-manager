<?php

declare(strict_types=1);

// This file should be in the global namespace

use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\StorageManager;

function storage(string $name = null): Storage
{
    /** @var StorageManager $dm */
    static $dm;

    if (!isset($dm)) {
        $dm = ServiceManager::get()->resolve(StorageManager::class);
    }

    return $dm->get($name);
}
