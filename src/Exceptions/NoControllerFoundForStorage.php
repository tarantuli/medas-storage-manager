<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\StorageManager\Interfaces\Storage;

class NoControllerFoundForStorage extends BaseException
{
    public function __construct(Storage $storage)
    {
        parent::__construct($storage->name(), $storage::class);
    }

    public function pattern(): string
    {
        return 'no controller found for storage %s of type %s';
    }
}
