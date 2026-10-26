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
        $unregistered = [];

        // Each migration is registered as soon as it has run (and the store exists), so a
        // later failing migration does not leave the executed ones unregistered
        $this->executor->execute(
            $directory,
            $this->processedMigrations(),
            function (Migration $migration) use (&$unregistered): void {
                $unregistered[] = $migration;

                if (!$this->controller->hasStore($this->store)) {
                    return;
                }

                foreach ($unregistered as $executedMigration) {
                    $this->registerExecution($executedMigration);
                }

                $unregistered = [];
            }, // An existing store makes the migration creating it unnecessary, also when its
            // execution was not registered
            fn(Migration $migration): bool
            => $this->isStoreMigration($migration::class) && $this->controller->hasStore($this->store));

        if ($unregistered) {
            throw new MigrationStoreDoesNotExist($unregistered);
        }
    }

    private function isStoreMigration(string $migrationClass): bool
    {
        $shortName = substr(strrchr('\\' . $migrationClass, '\\'), 1);

        return $shortName === MigrationStoreManager::STORE_MIGRATION_CLASS_NAME;
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
