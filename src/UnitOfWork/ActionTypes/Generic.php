<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork\ActionTypes;

use Medas\ServiceManager\AsSingleton;

class Generic implements ActionType
{
    use AsSingleton;

    public function priority(): int
    {
        return 2;
    }
}
