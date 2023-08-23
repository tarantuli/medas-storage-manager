<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\UnitOfWork\Action;

interface ActionExecutor
{
    public function execute(Action $action): void;
}
