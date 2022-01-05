<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\BaseAction;
use Medas\StorageManager\Interfaces\Storage;

class Query extends BaseAction
{
    public function __construct(
        public string $query,
        public array  $arguments = [],
        Storage       $storage = null
    )
    {
        $this->storage = $storage ?: storage();
    }

    public function execute(): void
    {
        $this->storage->execute($this);
    }
}
