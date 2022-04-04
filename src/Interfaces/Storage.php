<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Entities\SelectorActionBuilder;
use Medas\StorageManager\Entities\TypeSerializerFinder;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface Storage
{
    public function stores(): array;

    public function store(string $name): Store;

    public function migrationBuilder(): MigrationBuilder;

    public function beginTransaction(): void;

    public function rollbackTransaction(): void;

    public function commitTransaction(): void;

    public function lastGeneratedValue(): int|null;

    public function setName(string $name): void;

    public function deleteStore(string $name);

    public function typeSerializerFinder(): TypeSerializerFinder;

    public function selectorActionBuilder(): SelectorActionBuilder;

    public function fetchRecord(): StoreRecord|null;

    /** @return StoreRecord[] */
    public function fetchRecords(): array;
}
