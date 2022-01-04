<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\StorageManager\UnitOfWork\UnitOfWork;

interface Migration
{
    public function migrate(UnitOfWork $unitOfWork): void;

    public function undo(UnitOfWork $unitOfWork): void;
}
