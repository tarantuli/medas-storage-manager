<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\Structure;

use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\Structure\EntityStructureFinder;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\Structure\EntityWithDefaultValues;

class DefaultValuesTest extends BaseTestClass
{
    public function testStructureFinder(): Blueprint
    {
        $blueprint = service(EntityStructureFinder::class)->find(EntityWithDefaultValues::class);

        self::assertInstanceOf(Blueprint::class, $blueprint);

        return $blueprint;
    }

    /** @depends testStructureFinder */
    public function testDateTimeNotNullNoDefault(Blueprint $blueprint): void
    {
        $field = $blueprint->fieldByName('dateTimeNotNullNoDefault');

        self::assertFalse($field->isNullable);
        self::assertFalse($field->hasDefault);
    }

    /** @depends testStructureFinder */
    public function testDateTimeNullNoDefault(Blueprint $blueprint): void
    {
        $field = $blueprint->fieldByName('dateTimeNullNoDefault');

        self::assertTrue($field->isNullable);
        self::assertFalse($field->hasDefault);
    }

    /** @depends testStructureFinder */
    public function testDateTimeNullDefaultNull(Blueprint $blueprint): void
    {
        $field = $blueprint->fieldByName('dateTimeNullDefaultNull');

        self::assertTrue($field->isNullable);
        self::assertTrue($field->hasDefault);
        self::assertNull($field->default);
    }
}
