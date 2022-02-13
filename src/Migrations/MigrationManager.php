<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\ConfigManager\ConfigManager;
use Medas\FileSystem\DirectoryManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationsStore;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\UnitOfWork;
use Medas\StorageManager\UnitOfWork\UnitOfWorkExecutor;

#[Service]
class MigrationManager
{
    private array $migrations;

    public function __construct(
        private ConfigManager      $configManager,
        private DirectoryManager   $directoryManager,
        private UnitOfWorkExecutor $unitOfWorkExecutor,
    )
    {
    }

    public function migrate(string $directory): void
    {
        $directory = realpath($directory);

        $this->directoryManager->loadPhpFiles($directory);
        $this->processEntities($directory);
    }

    private function processEntities(string $directory)
    {
        $unitOfWork = new UnitOfWork();
        $this->migrations = $this->findMigrations($directory);

        foreach ($this->migrations as $migration) {
            if ($this->isExecuted($migration)) {
                continue;
            }

            $migration->migrate($unitOfWork);

            $this->registerExecution($migration);
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
        $migrationStore = $this->getMigrationsStore();

        return $migrationStore->fetchRecord(['migration' => $migration::class]) !== null;
    }

    private function getMigrationsStore(): Store
    {
        /** @var Store $store */
        $store = $this->configManager->getOptionValue(MigrationsStore::instance());

        if (!$store->exists()) {
            $blueprint = new Blueprint();
            $blueprint->name = $store->name();
            $migrationField = new Blueprint\Field('migration', 'varchar(255) not null');
            $blueprint->addField($migrationField);
            $blueprint->addField(new Blueprint\Field('migrated_at', 'datetime not null'));
            $index = new Blueprint\Index('migration');
            $index->fields[] = $migrationField;
            $blueprint->addIndex($index);
            $store->storage()->queryBuilder()->createTable($blueprint)->execute();
        }

        return $store;
    }

    private function registerExecution(Migration $migration): void
    {
        $migrationStore = $this->getMigrationsStore();

        $migrationStore->prepareCreate(['migration' => $migration::class, 'migrated_at' => date('Y-m-d H:i:s')])->execute();
    }

    public function processedMigrations(): array
    {
        return $this->migrations;
    }
}
