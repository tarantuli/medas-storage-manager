<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance\LinkingStore;

interface NamingStrategy
{
    public function determine(string $sourceTable): string;
}
