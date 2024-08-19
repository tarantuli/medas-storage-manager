<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

interface RecordMetaData
{
    /** @return FieldMetaData[] */
    public function fields(): array;

    /** @return string[] */
    public function primaryKeyFieldNames(): array;

    public function rowCount(): int;
}
