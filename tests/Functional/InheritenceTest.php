<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\EntityManager\Repository;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\Inheritence\{ArmorCard, WeaponCard};
use Medas\StorageManagerTest\MockUps\MockUpIds;

class InheritenceTest extends BaseTestClass
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('i_weapon_cards');
        storage()->controller()->deleteStore('i_armor_cards');
        storage()->controller()->deleteStore('i_cards');

        $migration = $this->createMigrationClassContent('Inheritence');

        self::assertStringContainsString('class Migration', $migration);
        $this->executeMigration($migration);
    }

    /** @depends testCreateMigration */
    public function testStoring(): MockUpIds
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

        $weaponId = $weapon1->id();
        $armorId = $armor1->id();

        self::assertNotEquals($armorId, $weaponId);

        em()->clear();

        $weapon2 = em()->get(WeaponCard::class, $weaponId);
        $armor2 = em()->get(ArmorCard::class, $armorId);

        self::assertInstanceOf(WeaponCard::class, $weapon2);
        self::assertInstanceOf(ArmorCard::class, $armor2);

        return new MockUpIds($weaponId, $armorId);
    }
}
