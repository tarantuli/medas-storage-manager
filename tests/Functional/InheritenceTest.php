<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\Inheritence\ArmorCard;
use Medas\StorageManagerTest\MockUps\Inheritence\WeaponCard;

class InheritenceTest extends BaseTestClass
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('i_cards');
        storage()->controller()->deleteStore('i_weapon_cards');
        storage()->controller()->deleteStore('i_armor_cards');

        $migration = $this->createMigrationClassContent('Inheritence');

        self::assertStringContainsString('class Migration', $migration);
        $this->executeMigration($migration);
    }

    /** @depends testCreateMigration */
    public function testStoring(): void
    {
        em()->autoPersistOnCreate();

        $weapon1 = em()->create(
            WeaponCard::class,
            ['name' => 'Iron blade', 'weaponType' => 'blade']
        );
        $armor1 = em()->create(
            ArmorCard::class,
            ['name' => 'Wooden shield', 'armorType' => 'shield']
        );

        self::assertNotEquals($armor1->id(), $weapon1->id());
    }
}
