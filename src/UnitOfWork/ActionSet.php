<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Interfaces\Collection;
use Medas\StorageManager\Interfaces\RecordSet;

/** @extends Collection<Action> */
interface ActionSet extends Collection
{
    public function recordSet(): RecordSet;

    public function setRecordSet(RecordSet $recordSet): void;
}
