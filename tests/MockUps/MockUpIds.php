<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps;

class MockUpIds
{
    public function __construct(
        public readonly int $weaponId,
        public readonly int $armorId,

    )
    {
    }
}
