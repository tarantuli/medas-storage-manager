<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface Store
{
    public function name(): string;

    public function storage(): Storage;
}
