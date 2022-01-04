<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Field;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Index;

class Changes
{
    /** @var \Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Field[] */
    public array $addFields = [];
    /** @var Field[] */
    public array $changeFields = [];
    /** @var Index[] */
    public array $indexes = [];

    public function __construct(public string $name)
    {
    }
}
