<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class UnhandledEnumBackingType extends BaseException
{
    public function __construct(string $enumClassName, \ReflectionNamedType $type)
    {
        parent::__construct($type->getName(), $enumClassName);
    }

    public function pattern(): string
    {
        return 'unhandled backing type %s for enum %s';
    }
}
