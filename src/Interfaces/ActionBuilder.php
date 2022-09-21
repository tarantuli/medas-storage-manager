<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\Action;

interface ActionBuilder
{
    public function createStore(Blueprint $blueprint): Action;
}
