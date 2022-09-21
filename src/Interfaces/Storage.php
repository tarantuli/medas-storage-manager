<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Entities\SelectorActionBuilder;
use Medas\StorageManager\Entities\TypeSerializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface Storage
{
    public function stores(): array;

    public function store(string $name): Store;

    public function transaction(): Transaction;

    public function lastGeneratedValue(): int|null;

    public function deleteStore(string $name);

    public function actionBuilder(): ActionBuilder;

    public function serializer(): TypeSerializer;

    public function migrationBuilder(): MigrationBuilder;

    public function selectorActionBuilder(): SelectorActionBuilder;
}
