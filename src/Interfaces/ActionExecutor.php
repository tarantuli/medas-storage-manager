<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\UnitOfWork\{Action, ActionSet};

interface ActionExecutor
{
    public function execute(Action $action, ActionSet|null $actionSet = null): void;

    public function executeSet(ActionSet $actionSet): void;
}
