<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class NotAMigrationFile extends BaseException
{
    public function __construct(string $filePath)
    {
        parent::__construct($filePath);
    }

    public function pattern(): string
    {
        return 'File does not contain a class implementing Migration: %s';
    }
}
