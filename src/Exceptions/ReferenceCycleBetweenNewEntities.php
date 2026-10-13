<?php

declare(strict_types=1);

namespace Medas\StorageManager\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ReferenceCycleBetweenNewEntities extends BaseException
{
    /** @param string[] $classNames */
    public function __construct(array $classNames)
    {
        parent::__construct(implode(', ', $classNames));
    }

    public function pattern(): string
    {
        return 'new entities refer to each other in a cycle, so none of them can be inserted first; '
            . 'save one of them in an earlier flush, or make one of the references nullable and set '
            . 'it afterwards. Not inserted: %s';
    }
}
