<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\UnitOfWork\ActionTypes\Generic;
use Medas\StorageManager\UnitOfWork\BaseAction;

class Query extends BaseAction
{
    public function __construct(
        public string $query,
        public array  $arguments = [],
        Storage       $storage = null
    )
    {
        $this->storage = $storage ?: storage();
        $this->type = Generic::instance();
    }

    public function execute(): void
    {
        $this->storage->execute($this);
    }
}
