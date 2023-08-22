<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

interface ActionExecutor
{
    public function execute(Action $action): void;
}
