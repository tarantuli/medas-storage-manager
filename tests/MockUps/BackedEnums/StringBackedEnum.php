<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\BackedEnums;

enum StringBackedEnum: string
{
    case Value1 = 'one';
    case Value2 = 'two';
}
