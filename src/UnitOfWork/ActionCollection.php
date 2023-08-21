<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Interfaces\Collection;
use Medas\StorageManager\Interfaces\RecordSet;

/** @extends Collection<Action> */
interface ActionCollection extends Collection
{
    public function execute(): void;

    public function recordSet(): RecordSet;
}
