<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Selector\Selector;
use Medas\StorageManager\UnitOfWork\Action;

interface SelectorActionBuilder
{
    public function build(Selector $selector, array $arguments): Action;
}
