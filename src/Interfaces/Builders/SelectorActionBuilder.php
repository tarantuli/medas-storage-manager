<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\EntityManager\Selector\Selector;
use Medas\StorageManager\UnitOfWork\ActionSet;

/**
 * If $doCount is true, then the result of the query must be a single record with a field named "count" containing the
 * matching record count.
 */
interface SelectorActionBuilder
{
    public function build(
        Selector $selector,
        array    $arguments,
        bool     $doCount = false,
        bool     $ignoreSlice = false
    ): ActionSet;
}
