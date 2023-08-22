<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface MigrationBuilder
{
    public function build(
        Storage          $storage,
        string           $className,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod
    ): bool;

    public function buildQueries(Storage $storage, Blueprint $expectedStructure): ActionSet|null;
}
