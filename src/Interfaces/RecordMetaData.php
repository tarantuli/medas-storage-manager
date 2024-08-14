<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface RecordMetaData
{
    /** @return FieldMetaData[] */
    public function fields(): array;
}
