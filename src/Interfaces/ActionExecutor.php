<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\UnitOfWork\Action;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface ActionExecutor
{
    public function execute(Action $action): void;

    public function executeSet(ActionSet $actionSet): void;
}
