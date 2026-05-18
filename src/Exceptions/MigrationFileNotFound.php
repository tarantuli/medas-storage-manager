<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class MigrationFileNotFound extends BaseException
{
    public function __construct(string $filePath)
    {
        parent::__construct($filePath);
    }

    public function pattern(): string
    {
        return 'Migration file not found: %s';
    }
}
