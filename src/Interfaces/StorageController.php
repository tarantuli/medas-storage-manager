<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Entities\SelectorActionBuilder;
use Medas\StorageManager\Entities\TypeSerializerFinder;
use Medas\StorageManager\Migrations\MigrationBuilder;

interface StorageController
{
    public function migrationBuilder(): MigrationBuilder;

    public function typeSerializerFinder(): TypeSerializerFinder;

    public function selectorActionBuilder(): SelectorActionBuilder;
}
