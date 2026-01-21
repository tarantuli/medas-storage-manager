<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Collections\GenericCollection;
use Medas\StorageManager\Interfaces\Record;

/**
 * @extends GenericCollection<Record>
 */
class RecordCollection extends GenericCollection
{
    public function clear(): void
    {
        $this->data = [];
        $this->index = 0;
    }
}
