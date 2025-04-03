<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\StorageManager\{Interfaces\Storage, Structure\Blueprint, UnitOfWork\ActionSet};

interface MigrationBuilder
{
    public function build(
        Storage          $storage,
        Blueprint        $expectedStructure,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod,
        bool             $ignoreExistingStructure = false,
    ): bool;

    public function buildActions(
        Storage   $storage,
        Blueprint $blueprint,
        bool      $ignoreExistingStructure = false,
    ): ActionSet;
}
