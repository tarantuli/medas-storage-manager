<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Blueprint\Blueprint;
use Medas\StorageManager\UnitOfWork\Action;

interface ActionBuilder
{
    public function createTable(Blueprint $blueprint): Action;
}
