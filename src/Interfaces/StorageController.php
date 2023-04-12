<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\Serializer;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function transaction(): Transaction;

    public function deleteStore(string $name): void;

    public function lastGeneratedValue(): int|null;

    public function serializer(): Serializer;

    public function actionBuilder(): ActionBuilder;

    public function migrationBuilder(): MigrationBuilder;
}
