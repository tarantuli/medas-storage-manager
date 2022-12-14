<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Collections\Collection;

interface ActionCollection extends Collection
{
    public function current(): Action;
}
