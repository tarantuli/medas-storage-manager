<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\StorageTests;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManagerTest\BaseTest;

abstract class AbstractStorageTest extends BaseTest
{
    protected Storage $storage;

    /**
     * This method should register a default storage with stores for the Group entity and the Person entity
     */
    abstract protected function prepare(): void;

    public function testPrepare(): void
    {
        $this->prepare();

        $storage = storage();

        self::assertInstanceOf(Storage::class, $storage);
        self::assertTrue($storage->store('groups')->exists());
        self::assertTrue($storage->store('persons')->exists());
    }
}
