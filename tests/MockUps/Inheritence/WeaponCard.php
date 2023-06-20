<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Inheritence;

use Medas\EntityManager\Attributes\Entity;

#[Entity('i_weapon_cards')]
class WeaponCard extends ItemCard
{
    public string $weaponType;

    public function __construct()
    {
        $this->itemType = 'weapon';
    }
}
