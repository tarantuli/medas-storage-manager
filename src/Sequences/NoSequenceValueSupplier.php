<?php

declare(strict_types=1);

namespace Medas\StorageManager\Sequences;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\Interfaces\Storage;

class NoSequenceValueSupplier extends BaseException
{
    public function __construct(Storage $storage)
    {
        parent::__construct($storage->name());
    }

    public function pattern(): string
    {
        return 'No sequence value supplier can handle storage %s';
    }
}
