<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\Interfaces\RecordSet;

/** @extends GenericCollection<Action> */
class ActionSet extends GenericCollection
{
    public static function fromAction(Action $action): static
    {
        return new static([$action]);
    }

    public RecordSet|null $lastRecordSet = null;
    public mixed $lastInsertId = null;
}
