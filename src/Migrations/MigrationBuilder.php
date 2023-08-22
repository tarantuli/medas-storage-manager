<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileBuilder\PhpClass\MethodDefinition;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface MigrationBuilder
{
    public function build(
        string           $className,
        MethodDefinition $migrateMethod,
        MethodDefinition $undoMethod
    ): bool;

    public function buildQueries(Blueprint $expectedStructure): ActionSet|null;
}
