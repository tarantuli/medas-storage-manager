<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\Selector\Parameter;
use Medas\StorageManager\Databases\Pdo\Database;

class ParaQuery
{
    public function __construct(
        public string   $query,
        /** @var Parameter[] */
        public array    $parameters,
        public Database $database,
    )
    {
    }
}
