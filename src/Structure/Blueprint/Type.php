<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Blueprint;

enum Type
{
    case Binary;
    case Boolean;
    case DateTime;
    case Integer;
    case Text;
}
