<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\{Attributes\Service, Events\DebugInformation, Interfaces\FileLoader};
use Medas\StorageManager\{
    Exceptions\MigrationException,
    UnitOfWork\UnitOfWork,
    UnitOfWork\UnitOfWorkExecutor
};

#[Service]
readonly class MigrationExecutor
{
    public function __construct(
        private FileLoader               $fileLoader,
        private FileToMigrationConverter $fileToMigrationConverter,
        private UnitOfWorkExecutor       $unitOfWorkExecutor,
    )
    {
    }

    public function execute(string $directory, array $alreadyExecutedMigrations): array
    {
        $directory = realpath($directory);
        $job = new Job($directory, $alreadyExecutedMigrations);

        $this->fileLoader->load($directory);
        $this->processDirectory($job);

        return $job->executedMigrations;
    }

    private function processDirectory(Job $job): void
    {
        $migrations = $this->findMigrations($job);

        foreach ($migrations as $fileName => $migration) {
            if (in_array($fileName, $job->alreadyExecutedMigrations, true)) {
                continue;
            }

            try {
                $unitOfWork = new UnitOfWork();

                $migration->migrate($unitOfWork);

                $this->unitOfWorkExecutor->execute($unitOfWork);
            }
            catch (\Throwable $e) {
                throw new MigrationException($fileName, $e->getMessage(), $job->executedMigrations);
            }

            $job->executedMigrations[] = $migration;

            dispatch(new DebugInformation('executied migration ' . $migration::class));
            dispatch(new ExecutedMigrationEvent($migration));
        }
    }

    /** @return Migration[] */
    private function findMigrations(Job $job): array
    {
        $migrations = [];

        foreach ($this->phpFilesIn($job->directory) as $fileInfo) {
            if (null === $migration = $this->fileToMigrationConverter->convert($fileInfo->getPathname())) {
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
}
