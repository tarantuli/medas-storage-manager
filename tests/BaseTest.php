<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\EntityManager\EntityManager;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Migrations\MigrationManager;
use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
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

    protected function entityManager(): EntityManager
    {
        $serviceManager = ServiceManager::get();
        return $serviceManager->resolve(EntityManager::class);
    }
}
