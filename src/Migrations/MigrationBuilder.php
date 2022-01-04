<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileBuilder\PhpClass\MethodDefinition;

interface MigrationBuilder
{
    public function build(string $className, MethodDefinition $migrateMethod, MethodDefinition $undoMethod): void;
}
