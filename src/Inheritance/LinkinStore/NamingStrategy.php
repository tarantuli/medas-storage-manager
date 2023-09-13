<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance\LinkinStore;

interface NamingStrategy
{
    public function determine(string $sourceTable): string;
}
