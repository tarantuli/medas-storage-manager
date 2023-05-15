<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Blueprint;

enum Type: string
{
    case Binary = 'binary';
    case Boolean = 'boolean';
    case DateTime = 'dateTime';
    case Float = 'float';
    case Integer = 'integer';
    case Text = 'text';
    case Collection = 'collection';
}
