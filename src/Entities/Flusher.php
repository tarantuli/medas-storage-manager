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
        private Persister          $entityPersister,
        private UnitOfWorkExecutor $unitOfWorkExecutor,
    )
    {
    }

    public function flush(array $entities, \SplObjectStorage $savedStates): void
    {
        $unitOfWork = new UnitOfWork();

        foreach ($entities as $entity) {
            $this->entityPersister->prepare(
                $entity,
                $savedStates[$entity] ?? null,
                $unitOfWork
            );
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }
}
