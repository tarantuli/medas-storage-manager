<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\{Attributes\Service, Interfaces\FileLoader};
use Medas\StorageManager\{
    Exceptions\MigrationAlreadyExecuted,
    Exceptions\MigrationFileNotFound,
    Exceptions\MigrationStoreDoesNotExist,
    Exceptions\NoDefaultStorageFound,
    Exceptions\NotAMigrationFile,
    Interfaces\StorageController,
    Interfaces\Store,
    StorageManager,
    UnitOfWork\UnitOfWorkExecutor
};

#[Service]
readonly class MigrationManager
{
    private Store|null $store;
    private StorageController|null $controller;

    public function __construct(
        private FileLoader               $fileLoader,
        private FileToMigrationConverter $fileToMigrationConverter,
        private MigrationExecutor        $executor,
        private UnitOfWorkExecutor       $unitOfWorkExecutor,
        MigrationStoreManager            $migrationStoreManager,
        StorageManager                   $storageManager,
    )
    {
        try {
            $this->store = $migrationStoreManager->store();
            $this->controller = $storageManager->controller();
        }
        catch (NoDefaultStorageFound) {
            $this->store = null;
            $this->controller = null;
        }
    }

    public function migrate(string $directory): void
    {
        $migrations = $this->executor->execute($directory, $this->processedMigrations());

        if (!$this->controller->hasStore($this->store)) {
            throw new MigrationStoreDoesNotExist($migrations);
        }

        foreach ($migrations as $migration) {
            $this->registerExecution($migration);
        }
    }

    public function markMigrated(string $filePath): void
    {
        if (!$this->controller->hasStore($this->store)) {
            throw new MigrationStoreDoesNotExist();
        }

        // Resolve relative paths against the current working directory
        $resolved = realpath($filePath);

        if ($resolved === false) {
            throw new MigrationFileNotFound($filePath);
        }

        // Load the file so the class becomes available if it is not PSR-4 autoloaded
        require_once $resolved;

        $migration = $this->fileToMigrationConverter->convert($resolved);

        if (in_array($resolved, $this->processedMigrations(), true)) {
            throw new MigrationAlreadyExecuted($migration::class);
        }

        if ($migration === null) {
            throw new NotAMigrationFile($resolved);
        }

        $this->registerExecution($migration);
    }

    private function registerExecution(Migration $migration): void
    {
        $actions = $this->controller->actionBuilders()->insert()
            ->build($this->store, ['migration' => $migration::class, 'migratedAt' => new \DateTime()]);

        $this->controller->actionExecutor()->executeSet($actions);
    }

    /** 
     * Returns an array of class names of migrations that have been processed.
     * 
     * @return string[] 
     */
    public function processedMigrations(): array
    {
        if (!$this->controller->hasStore($this->store)) {
            return [];
        }

        $recordSet = $this->controller->recordFetchers()->filteredFetcher()
            ->fetch($this->store);

        $processed = [];

        while ($record = $recordSet->fetchRecord()) {
            $processed[] = $record->data()['migration'];
        }

        return $processed;
    }
}
