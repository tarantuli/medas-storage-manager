<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Entities\TypeSerializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function transaction(): Transaction;

    public function deleteStore(string $name): void;

    public function serializer(): TypeSerializer;

    public function actionBuilder(): ActionBuilder;

    public function migrationBuilder(): MigrationBuilder;
}
