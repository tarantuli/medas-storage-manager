<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Entities\{Changes, Flusher as FlusherInterface};
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\UnitOfWork\{UnitOfWork, UnitOfWorkExecutor};

#[Service]
class Flusher implements FlusherInterface
{
    public function __construct(
        private readonly Persister          $entityPersister,
        private readonly UnitOfWorkExecutor $unitOfWorkExecutor,
    )
    {
    }

    public function flush(Changes $changes): void
    {
        $unitOfWork = new UnitOfWork();

        foreach ($changes->creates() as $entity) {
            $this->entityPersister->prepareCreate($entity, $unitOfWork);
        }

        foreach ($changes->updates() as [$entity, $diff]) {
            $this->entityPersister->prepareUpdate($entity, $diff, $unitOfWork);
        }

        foreach ($changes->deletes() as $entity) {
            $this->entityPersister->prepareDelete($entity, $unitOfWork);
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }
}
