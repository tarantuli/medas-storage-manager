<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Blueprint;

use Medas\EntityManager\Types\Integer;

class Field
{
    public function __construct(
        public string $name,
        public Type   $type,
        public bool   $isNullable = false,
        public bool   $isGenerated = false,
        public bool   $isCreationTimestamp = false,
        public bool   $isModificationTimestamp = false,
        public bool   $hasDefault = false,
        public mixed  $default = null,
        public int    $minValue = 0,
        public int    $maxValue = Integer::UNSIGNED_4_BYTE_MAX,
        public int    $minLength = 0,
        public int    $maxLength = Integer::UNSIGNED_1_BYTE_MAX,
    )
    {
    }
}
