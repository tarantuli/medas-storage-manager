<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Composer\Autoload\ClassLoader;
use Medas\Core\{Attributes\Service, Interfaces\FileLoader};
use Medas\StorageManager\{
    Exceptions\MigrationException,
    Interfaces\Store,
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

    /** @return string[] */
    public function processedMigrations(): array
    {
        $store = $this->migrationStoreManager->store();
        $recordSet = $this->storageManager->controller()->recordFetchers()->filteredFetcher()
            ->fetch($store);

        $processed = [];

        while ($record = $recordSet->fetchRecord()) {
            $processed[] = $record->data()['migration'];
        }

        return $processed;
    }

    public function migrate(string $directory): void
    {
        $directory = realpath($directory);

        $this->fileLoader->load($directory);
        $this->processDirectory($directory, $this->migrationStoreManager->store());
    }

    private function processDirectory(string $directory, Store $store): void
    {
        $migrations = $this->findMigrations($directory);

        foreach ($migrations as $fileName => $migration) {
            if ($this->isExecuted($migration, $store)) {
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

            $this->registerExecution($migration, $store);
        }
    }

    /** @return Migration[] */
    private function findMigrations(string $directory): array
    {
        $migrations = [];

        foreach ($this->phpFilesIn($directory) as $fileInfo) {
            if (null === $migration = $this->migrationFromFile($fileInfo->getPathname())) {
                continue;
            }

            $migrations[$migration::class] = $migration;
        }

        ksort($migrations);

        return $migrations;
    }

    /** @return \RecursiveIteratorIterator<\RecursiveDirectoryIterator> */
    private function phpFilesIn(string $directory): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            $directory,
            \FilesystemIterator::SKIP_DOTS
        ));
    }

    private function migrationFromFile(string $filePath): Migration|null
    {
        if (!str_ends_with($filePath, '.php')) {
            return null;
        }

        $className = $this->classNameFromFile($filePath);

        if ($className === null || !class_exists($className)) {
            return null;
        }

        $class = new \ReflectionClass($className);

        if (!$class->implementsInterface(Migration::class)) {
            return null;
        }

        return new $className();
    }

    private function classNameFromFile(string $filePath): string|null
    {
        foreach (spl_autoload_functions() as $loader) {
            if (!is_array($loader) || !$loader[0] instanceof ClassLoader) {
                continue;
            }

            foreach ($loader[0]->getPrefixesPsr4() as $namespace => $dirs) {
                foreach ($dirs as $dir) {
                    $dir = rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                    if (!str_starts_with($filePath, $dir)) {
                        continue;
                    }

                    $relative = substr($filePath, strlen($dir));

                    return $namespace
                        . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));
                }
            }
        }

        return null;
    }

    private function isExecuted(Migration $migration, Store $store): bool
    {
        $recordSet = $this->storageManager->controller()->recordFetchers()->filteredFetcher()
            ->fetch($store, ['migration' => $migration::class]);

        return $recordSet->fetchRecord() !== null;
    }

    private function registerExecution(Migration $migration, Store $store): void
    {
        $storageController = $this->storageManager->controller();
        $actions = $storageController->actionBuilders()->insert()
            ->build($store, ['migration' => $migration::class, 'migratedAt' => new \DateTime()]);

        $storageController->actionExecutor()->executeSet($actions);
    }
}
