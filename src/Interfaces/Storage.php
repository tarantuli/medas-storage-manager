<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Storage
{
    public function stores(): array;

    public function store(string $name): Store;

    public function controller(): StorageController;
}
