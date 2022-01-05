<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork\ActionTypes;

interface ActionType
{
    public function priority(): int;
}
