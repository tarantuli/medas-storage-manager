<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional\StorageTests;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Migrations\MigrationBuildManager;
use Medas\StorageManager\Migrations\MigrationManager;
use PHPUnit\Framework\TestCase;

abstract class AbstractStorageTest extends TestCase
{
    protected Storage $storage;

    /**
     * This method should register a storage named "test" with stores for the Group entity and the Person entity
     */
    abstract protected function prepare(): void;

    public function testPrepare(): void
    {
        $this->prepare();

        $storage = storage('test');

        self::assertInstanceOf(Storage::class, $storage);
        self::assertTrue($storage->store('groups')->exists());
        self::assertTrue($storage->store('persons')->exists());
    }

    protected function createMigrationClassContent(): string
    {
        $buildManager = service(MigrationBuildManager::class);
        $directory = realpath(__DIR__ . '/../../MockUps/Relations');

        return $buildManager->createMigrationClass($directory);
    }

    protected function executeMigration(string $migration): void
    {
        preg_match('/class (Migration\d+)/', $migration, $match);
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
        $fileName = $directory . DIRECTORY_SEPARATOR . $match[1] . '.php';

        // Prepare the migration test directory
        if (!file_exists($directory)) {
            mkdir($directory);
        }
        else {
            foreach (glob($directory . DIRECTORY_SEPARATOR . '*') as $existingFile) {
                unlink($existingFile);
            }
        }

        // Execute the migration
        file_put_contents($fileName, $migration);
        $manager = service(MigrationManager::class);
        $manager->migrate($directory);

        // Remove the test directory
        unlink($fileName);
        rmdir($directory);
    }
}
