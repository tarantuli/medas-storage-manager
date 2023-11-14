<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\Service;
use Medas\FileSystem\DirectoryManager;
use Medas\StorageManager\{StorageManager, UnitOfWork\UnitOfWork, UnitOfWork\UnitOfWorkExecutor};

#[Service]
class MigrationManager
{
    private array $processedMigrations = [];

    public function __construct(
        private readonly DirectoryManager      $directoryManager,
        private readonly MigrationStoreManager $migrationStoreManager,
        private readonly StorageManager        $storageManager,
        private readonly UnitOfWorkExecutor    $unitOfWorkExecutor,
    )
    {
    }

    public function migrate(string $directory): void
    {
        $directory = realpath($directory);

        $this->directoryManager->loadPhpFiles($directory);
        $this->processEntities($directory);
    }

    private function processEntities(string $directory): void
    {
        $unitOfWork = new UnitOfWork();
        $migrations = $this->findMigrations($directory);

        foreach ($migrations as $migration) {
            if ($this->isExecuted($migration)) {
                continue;
            }

            $migration->migrate($unitOfWork);

            $this->processedMigrations[] = $migration;
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);

        foreach ($this->processedMigrations as $migration) {
            $this->registerExecution($migration);
        }
    }

    /** @return Migration[] */
    private function findMigrations(string $directory): array
    {
        $migrations = [];

        foreach (get_declared_classes() as $className) {
            if (null === $migration = $this->createMigration($className, $directory)) {
                continue;
            }

            $migrations[$migration::class] = $migration;
        }

        ksort($migrations);

        return $migrations;
    }

    private function createMigration(string $className, string $directory): Migration|null
    {
        $class = new \ReflectionClass($className);

        if (!$class->getFileName()) {
            return null;
        }

        if (!str_starts_with($class->getFileName(), $directory)) {
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

    private function isExecuted(Migration $migration): bool
    {
        return $this->storageManager->controller()->recordFetchers()->filteredFetcher()
            ->fetch($this->migrationStoreManager->get(), ['migration' => $migration::class])
            ->hasRecords();
    }

    private function registerExecution(Migration $migration): void
    {
        $storageController = $this->storageManager->controller();
        $actions = $storageController->actionBuilders()->insert()
            ->build(
                $this->migrationStoreManager->get(),
                ['migration' => $migration::class, 'migratedAt' => date('Y-m-d H:i:s')]
            );

        $storageController->actionExecutor()->executeSet($actions);
    }

    public function processedMigrations(): array
    {
        return $this->processedMigrations;
    }
}
