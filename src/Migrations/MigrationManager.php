<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\{Attributes\Service, Interfaces\FileLoader};
use Medas\StorageManager\{
    Exceptions\MigrationException,
    StorageManager,
    UnitOfWork\UnitOfWork,
    UnitOfWork\UnitOfWorkExecutor
};

#[Service]
readonly class MigrationManager
{
    public function __construct(
        private FileLoader            $fileLoader,
        private MigrationStoreManager $migrationStoreManager,
        private StorageManager        $storageManager,
        private UnitOfWorkExecutor    $unitOfWorkExecutor,
    )
    {
    }

    public function migrate(string $directory): void
    {
        $directory = realpath($directory);

        $this->fileLoader->load($directory);
        $this->processDirectory($directory);
    }

    private function processDirectory(string $directory): void
    {
        $migrations = $this->findMigrations($directory);

        foreach ($migrations as $fileName => $migration) {
            if ($this->isExecuted($migration)) {
                continue;
            }

            try {
                $unitOfWork = new UnitOfWork();

                $migration->migrate($unitOfWork);

                $this->unitOfWorkExecutor->execute($unitOfWork);
            }
            catch (\Throwable $e) {
                throw new MigrationException($fileName, $e->getMessage());
            }

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
}
