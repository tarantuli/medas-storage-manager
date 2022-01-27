<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\StorageManager\Databases\Pdo\Record;

interface StoreRecord
{

    public function data(): array;
}
