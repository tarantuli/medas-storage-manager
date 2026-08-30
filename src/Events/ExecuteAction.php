<?php

declare(strict_types=1);

namespace Medas\StorageManager\Events;

use Medas\StorageManager\UnitOfWork\Action;

class ExecuteAction
{
    public function __construct(
        public Action $action,
    )
    {
    }
}
