<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\{Changes, Flusher as FlusherInterface};
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

        foreach ($changes->createdEntities() as $entity) {
            $this->entityPersister->prepareCreate($entity, $unitOfWork);
        }

        foreach ($changes->updatedEntities() as $entity) {
            $this->entityPersister->prepareUpdate($entity, $changes->entityChanges($entity), $unitOfWork);
        }

        foreach ($changes->deletedEntities() as $entity) {
            $this->entityPersister->prepareDelete($entity, $unitOfWork);
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }
}
