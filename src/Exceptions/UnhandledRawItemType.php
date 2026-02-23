<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class UnhandledRawItemType extends BaseException
{
    public function __construct(string $rawItemType)
    {
        parent::__construct($rawItemType);
    }

    public function pattern(): string
    {
        return 'A collection can only contain objects, found content type %s instead';
    }
}
