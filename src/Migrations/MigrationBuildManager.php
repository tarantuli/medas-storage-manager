<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\{Attributes\Service, Interfaces\DirectoryCreator, Interfaces\FileLoader};
use Medas\EntityManager\Attributes\Entity;
use Medas\FileBuilder\{
    PhpClass\MethodDefinition,
    PhpClass\ParameterDefinition,
    PhpClass\PhpClassDefinition,
    PhpClassBuilder
};
use Medas\StorageManager\{StorageManager, Structure\EntityStructureFinder, UnitOfWork\UnitOfWork};

#[Service]
readonly class MigrationBuildManager
{
    public function __construct(
        private DirectoryCreator         $directoryCreator,
        private EntityStructureFinder    $entityStructureFinder,
        private FileLoader               $fileLoader,
        private PhpClassBuilder          $phpClassBuilder,
        private StorageManager           $storageManager,
        private StoredEntityDeterminator $storedEntityDeterminator,
    )
    {
    }

    public function createMigration(array $sourceDirectories, string $migrationsDirectory): string|null
    {
        $job = $this->createMigrationClassCode($sourceDirectories);

        if ($job->migrationNeeded) {
            $this->directoryCreator->create($migrationsDirectory);

            $filePath = $migrationsDirectory . DIRECTORY_SEPARATOR . $job->className . '.php';

            file_put_contents($filePath, $job->classCode);

            return $filePath;
        }

        return null;
    }

    public function createMigrationClassCode(array $sourceDirectories): Job
    {
        $job = new Job($sourceDirectories);

        foreach ($job->sourceDirectories as $i => $directory) {
            $job->sourceDirectories[$i] = realpath($directory);
        }

        $this->initializeClass($job);
        $this->initializeMethods($job);

        foreach ($job->sourceDirectories as $directory) {
            $this->fileLoader->load($directory);
        }

        $this->processEntities($job);

        $job->classCode = $job->migrationNeeded
            ? $this->phpClassBuilder->build($job->migrationClass)
            : null;

        return $job;
    }

    private function initializeClass(Job $job): void
    {
        $now = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''))
            ->format('YmdHisu');

        $job->className = 'Migration' . $now;
        $job->migrationClass = new PhpClassDefinition($job->className, 'Medas\\Migrations');
        $job->migrationClass->implements[] = Migration::class;
    }

    private function initializeMethods(Job $job): void
    {
        $this->initializeMigrateMethod($job);
        $this->initializeUndoMethod($job);

        $job->migrationClass->methods = [$job->migrateMethod, $job->undoMethod];
    }

    private function initializeMigrateMethod(Job $job): void
    {
        $job->migrateMethod = new MethodDefinition('migrate');
        $job->migrateMethod->parameters = [new ParameterDefinition(UnitOfWork::class, 'unitOfWork')];
        $job->migrateMethod->returnTypes = ['void'];
        $job->migrateMethod->body = '';
    }

    private function initializeUndoMethod(Job $job): void
    {
        $job->undoMethod = new MethodDefinition('undo');
        $job->undoMethod->parameters = [new ParameterDefinition(UnitOfWork::class, 'unitOfWork')];
        $job->undoMethod->returnTypes = ['void'];
        $job->undoMethod->body = '';
    }

    private function processEntities(Job $job): void
    {
        $job->migrationNeeded = false;

        foreach (get_declared_classes() as $className) {
            if (null === $entity = $this->storedEntityDeterminator->determine($className, $job->sourceDirectories)) {
                continue;
            }

            $this->processEntity($job, $className, $entity);
        }
    }

    private function processEntity(Job $job, string $className, Entity $entity): void
    {
        $expectedStructure = $this->entityStructureFinder->find($className);
        $needed = $this->storageManager->controller($entity->storage)->migrationBuilder()
            ->build(
                $this->storageManager->byName($entity->storage),
                $expectedStructure,
                $job->migrateMethod,
                $job->undoMethod
            );

        $job->migrationNeeded = $job->migrationNeeded || $needed;
    }
}
