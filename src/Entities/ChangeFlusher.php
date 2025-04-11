<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{Entities\Flusher, Snapshots\Changes};
use Medas\StorageManager\UnitOfWork\{UnitOfWork, UnitOfWorkExecutor};

#[Service]
readonly class ChangeFlusher implements Flusher
{
    public function __construct(
        private EntityPersister    $entityPersister,
        private UnitOfWorkExecutor $unitOfWorkExecutor,
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
            $this->entityPersister->prepareUpdate(
                $entity,
                $changes->entityChanges($entity),
                $unitOfWork
            );
        }

        foreach ($changes->deletedEntities() as $entity) {
            $this->entityPersister->prepareDelete($entity, $unitOfWork);
        }

        $this->unitOfWorkExecutor->execute($unitOfWork);
    }
}
