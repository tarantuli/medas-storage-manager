<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\StorageManager\Interfaces\Storage;

interface MigrationBuilder
{
    public function build(Storage $storage, string $className, MethodDefinition $migrateMethod, MethodDefinition $undoMethod): void;
}
