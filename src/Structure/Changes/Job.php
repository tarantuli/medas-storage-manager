<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Changes;

use Medas\StorageManager\Structure\Blueprint;

class Job
{
    public function __construct(
        public Blueprint $expected,
        public Blueprint $existing,
        public Changes   $changes,
        public bool      $foundChanges = false,
    )
    {
    }
}
