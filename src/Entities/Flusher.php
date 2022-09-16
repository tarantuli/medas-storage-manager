<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\Flusher as FlusherInterface;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\UnitOfWork\UnitOfWork;
use Medas\StorageManager\UnitOfWork\UnitOfWorkExecutor;

#[Service]
class Flusher implements FlusherInterface
{
    public function __construct(
        private readonly Persister          $entityPersister,
        private readonly UnitOfWorkExecutor $unitOfWorkExecutor,
    )
    {
    }

    public function flush(array $entities, \SplObjectStorage $savedStates, array $entitiesToDelete): void
    {
        $unitOfWork = new UnitOfWork();

        foreach ($entities as $entity) {
            $this->entityPersister->prepare(
                $entity,
                $savedStates[$entity] ?? null,
                $unitOfWork
            );
        }

        foreach ($entitiesToDelete as $entity) {
            $this->entityPersister->prepareDelete($entity, $unitOfWork);
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }
}
