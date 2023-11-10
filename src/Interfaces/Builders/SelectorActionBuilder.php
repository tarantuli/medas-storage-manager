<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces\Builders;

use Medas\EntityManager\Selector\Selector;
use Medas\StorageManager\UnitOfWork\ActionSet;

interface SelectorActionBuilder
{
    public function build(Selector $selector, array $arguments): ActionSet;
}
