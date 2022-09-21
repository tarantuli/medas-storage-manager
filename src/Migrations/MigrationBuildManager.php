<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\EntityManager\Attributes\Entity;
use Medas\FileBuilder\PhpClass\{MethodDefinition, ParameterDefinition, PhpClassDefinition};
use Medas\FileBuilder\PhpClassBuilder;
use Medas\FileSystem\DirectoryManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\UnitOfWork\UnitOfWork;

#[Service]
class MigrationBuildManager
{
    private string $className;
    private PhpClassDefinition $migrationClass;
    private MethodDefinition $migrateMethod;
    private MethodDefinition $undoMethod;

    public function __construct(
        private readonly DirectoryManager $directoryManager,
        private readonly PhpClassBuilder  $phpClassBuilder,
    )
    {
    }

    public function createMigration(string $sourceDirectory, string $migrationsDirectory): void
    {
        $classCode = $this->createMigrationClass(realpath($sourceDirectory));
        $this->directoryManager->create($migrationsDirectory);
        file_put_contents($migrationsDirectory . DIRECTORY_SEPARATOR . $this->className . '.php', $classCode);
    }

    public function createMigrationClass(string $directory): string
    {
        $directory = realpath($directory);

        $this->initializeClass();
        $this->initializeMethods();
        $this->directoryManager->loadPhpFiles($directory);
        $this->processEntities($directory);

        return $this->phpClassBuilder->build($this->migrationClass);
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

    private function processEntities(string $directory): void
    {
        foreach (get_declared_classes() as $className) {
            if (null === $entity = $this->determineStoredEntity($className, $directory)) {
                continue;
            }

            $this->processEntity($className, $entity);
        }
    }

    private function determineStoredEntity(string $className, string $directory): Entity|null
    {
        $class = new \ReflectionClass($className);

        if (!$class->getFileName() || !str_starts_with($class->getFileName(), $directory)) {
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
        $storage = storage($entity->storage);
        $storage->migrationBuilder()
            ->build($storage, $className, $this->migrateMethod, $this->undoMethod);
    }
}
