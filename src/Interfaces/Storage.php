<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Storage
{
    public function controller(): StorageController;

    public function stores(): array;

    public function store(string $name): Store;

    public function beginTransaction(): void;

    public function rollbackTransaction(): void;

    public function commitTransaction(): void;

    public function lastGeneratedValue(): int|null;

    public function deleteStore(string $name);

    public function actionBuilder(): ActionBuilder;
}
