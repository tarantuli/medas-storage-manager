<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance\LinkinStore;

use Medas\Core\Attributes\Service;

#[Service]
class AppendFixedSuffix implements NamingStrategy
{
    public function determine(string $sourceTable): string
    {
        return $sourceTable . '__original_class';
    }
}
