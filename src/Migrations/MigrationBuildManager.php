<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Attributes\Entity;
use Medas\FileBuilder\{
    PhpClass\MethodDefinition,
    PhpClass\ParameterDefinition,
    PhpClass\PhpClassDefinition,
    PhpClassBuilder
};
use Medas\FileSystem\DirectoryManager;
use Medas\StorageManager\{StorageManager, Structure\EntityStructureFinder, UnitOfWork\UnitOfWork};

#[Service]
class MigrationBuildManager
{
    private string $className;
    private string|null $classCode;
    private bool $migrationNeeded;
    private PhpClassDefinition $migrationClass;
    private MethodDefinition $migrateMethod;
    private MethodDefinition $undoMethod;

    public function __construct(
        private readonly DirectoryManager      $directoryManager,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly PhpClassBuilder       $phpClassBuilder,
        private readonly StorageManager        $storageManager,
    )
    {
    }

    public function createMigration(array $sourceDirectories, string $migrationsDirectory): string|null
    {
        $this->createMigrationClass($sourceDirectories);

        if ($this->migrationNeeded) {
            $this->directoryManager->create($migrationsDirectory);

            $filePath = $migrationsDirectory . DIRECTORY_SEPARATOR . $this->className . '.php';

            file_put_contents($filePath, $this->classCode);

            return $filePath;
        }

        return null;
    }

    public function createMigrationClass(array $directories): string|null
    {
        foreach ($directories as $i => $directory) {
            $directories[$i] = realpath($directory);
        }

        $this->initializeClass();
        $this->initializeMethods();

        foreach ($directories as $directory) {
            $this->directoryManager->loadPhpFiles($directory);
        }

        $this->processEntities($directories);

        return $this->classCode = $this->migrationNeeded ? $this->phpClassBuilder->build($this->migrationClass) : null;
    }

    private function initializeClass(): void
    {
        $now = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))
            ->format('YmdHisu');

        $this->className = 'Migration' . $now;
        $this->migrationClass = new PhpClassDefinition($this->className, 'Medas\\Migrations');
        $this->migrationClass->implements[] = Migration::class;
    }

    private function initializeMethods(): void
    {
        $this->initializeMigrateMethod();
        $this->initializeUndoMethod();

        $this->migrationClass->methods = [$this->migrateMethod, $this->undoMethod];
    }

    private function initializeMigrateMethod(): void
    {
        $this->migrateMethod = new MethodDefinition('migrate');
        $this->migrateMethod->parameters = [new ParameterDefinition(UnitOfWork::class, 'unitOfWork')];
        $this->migrateMethod->returnTypes = ['void'];
        $this->migrateMethod->body = '';
    }

    private function initializeUndoMethod(): void
    {
        $this->undoMethod = new MethodDefinition('undo');
        $this->undoMethod->parameters = [new ParameterDefinition(UnitOfWork::class, 'unitOfWork')];
        $this->undoMethod->returnTypes = ['void'];
        $this->undoMethod->body = '';
    }

    private function processEntities(array $directories): void
    {
        $this->migrationNeeded = false;

        foreach (get_declared_classes() as $className) {
            if (null === $entity = $this->determineStoredEntity($className, $directories)) {
                continue;
            }

            $this->processEntity($className, $entity);
        }
    }

    private function determineStoredEntity(string $className, array $directories): Entity|null
    {
        $class = new \ReflectionClass($className);

        if (!$class->getFileName()) {
            return null;
        }

        $foundDirectory = false;

        foreach ($directories as $directory) {
            if (str_starts_with($class->getFileName(), $directory)) {
                $foundDirectory = true;

                break;
            }
        }

        if (!$foundDirectory) {
            return null;
        }

        if (!$entity = attribute(Entity::class, $class)) {
            return null;
        }

        if ($entity->storage === null && $entity->store === null) {
            return null;
        }

        return $entity;
    }

    private function processEntity(string $className, Entity $entity): void
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $needed = $this->storageManager->controller($entity->storage)->migrationBuilder()
            ->build(
                $this->storageManager->byName($entity->storage),
                $expectedStructure,
                $this->migrateMethod,
                $this->undoMethod
            );

        $this->migrationNeeded = $this->migrationNeeded || $needed;
    }
}
