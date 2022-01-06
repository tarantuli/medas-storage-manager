<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\FileSystem\DirectoryManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\UnitOfWork\UnitOfWork;
use Medas\StorageManager\UnitOfWork\UnitOfWorkExecutor;

#[Service]
class MigrationManager
{
    public function __construct(
        private DirectoryManager   $directoryManager,
        private UnitOfWorkExecutor $unitOfWorkExecutor,
    )
    {
    }

    public function migrate(string $directory): void
    {
        $this->directoryManager->loadPhpFiles($directory);
        $this->processEntities($directory);
    }

    private function processEntities(string $directory)
    {
        $unitOfWork = new UnitOfWork();
        $migrations = $this->findMigrations($directory);

        foreach ($migrations as $migration) {
            $migration->migrate($unitOfWork);
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }

    /** @return Migration[] */
    private function findMigrations(string $directory): array
    {
        $migrations = [];
        foreach (get_declared_classes() as $className) {
            if (null === $migration = $this->createMigration($className, $directory)) {
                continue;
            }

            $migrations[] = $migration;
        }

        return $migrations;

    }

    private function createMigration(string $className, string $directory): Migration|null
    {
        $class = new \ReflectionClass($className);

        if (!$class->getFileName() || !str_starts_with($class->getFileName(), $directory)) {
            return null;
        }

        if (!file_exists($class->getFileName())) {
            return null;
        }

        if (!$class->implementsInterface(Migration::class)) {
            return null;
        }

        return new $className();
    }
}
