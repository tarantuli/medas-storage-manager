<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\IdentifierQuoters;

interface IdentifierQuoter
{
    public function quote(string $identifier): string;
}
