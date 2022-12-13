<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest;

use Medas\StorageManager\Migrations\{MigrationBuildManager, MigrationManager};
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected function createMigrationClassContent(string $directory): string|null
    {
        $buildManager = service(MigrationBuildManager::class);
        $realDirectory = realpath(__DIR__ . '/MockUps/' . $directory);

        if ($realDirectory === false) {
            throw new \Exception('directory "' . __DIR__ . '/MockUps/' . $directory . '" does not exist');
        }

        return $buildManager->createMigrationClass($realDirectory);
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
