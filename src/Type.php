<?php

declare(strict_types=1);

namespace Medas\StorageManager;

enum Type: string
{
    case Binary = 'binary';
    case Boolean = 'boolean';
    case DateTime = 'dateTime';
    case Date = 'date';
    case Float = 'float';
    case Integer = 'integer';
    case Text = 'text';
    case Collection = 'collection';
}
