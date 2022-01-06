<?php

declare(strict_types=1);

namespace Medas\Test;

use Medas\StorageManager\Migrations\MigrationManager;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    protected function executeMigration(string $migration): void
    {
        $directory = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';
        $fileName = $directory . DIRECTORY_SEPARATOR . 'migration.php';

        // Prepare the migration test directory
        if (!file_exists($directory)) {
            mkdir($directory);
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
