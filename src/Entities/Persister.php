<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Snapshots\{Snapshot, SnapshotManager};
use Medas\EntityManager\Types\Guid;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Values\Interfaces\GuidProvider;
use Medas\StorageManager\Interfaces\{Storage, Store};
use Medas\StorageManager\UnitOfWork\{UnitOfWork, UnitOfWorkManager};

#[Service]
class Persister
{
    public function __construct(
        private readonly DataSerializer    $dataSerializer,
        private readonly Fetcher           $fetcher,
        private readonly GuidProvider|null $guidProvider,
        private readonly MetaDataManager   $metaDataManager,
        private readonly SnapshotManager   $snapshotManager,
        private readonly UnitOfWorkManager $unitOfWorkManager,
        private readonly ValueGetter       $valueGetter,
    )
    {
    }

    public function prepare(object $entity, Snapshot|null $initialState, UnitOfWork $unitOfWork): void
    {
        if ($initialState === null) {
            // The entity is new
            $this->prepareCreate($entity, $unitOfWork);
            return;
        }

        $changedValues = $this->snapshotManager->findChanges($entity, $initialState);

        if ($changedValues === []) {
            // The entity hasn't changed
            return;
        }

        $this->prepareUpdate($entity, $changedValues, $unitOfWork);
    }

    private function prepareCreate(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);
        $values = [];

        foreach ($metaData->properties as $property) {
            $foundValue = false;
            $value = null;

            if ($property->isCreationTimestamp || $property->isModificationTimestamp) {
                $value = new \DateTime();
                $property->reflection->setValue($entity, $value);
                $foundValue = true;
            }
            elseif ($property->reflection->isInitialized($entity)) {
                $value = $property->reflection->getValue($entity);
                $foundValue = true;
            }
            elseif ($property->type instanceof Guid) {
                if ($this->guidProvider === null) {
                    throw new \Exception('no GuidProvider registered, but it is needed. Try for instance morphp/medas-ramsey-uuid-bridge');
                }

                $value = $this->guidProvider->create();
                $property->reflection->setValue($entity, $value);
                $foundValue = true;
            }

            if ($foundValue) {
                $values[$property->name] = $value;
            }
        }

        $this->dataSerializer->serialize($metaData, $values);

        $this->unitOfWorkManager->queueCreate(
            $unitOfWork,
            $this->getStore($metaData),
            $values,
            $this->generatedValueSetter($metaData, $entity)
        );
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    private function generatedValueSetter(MetaData $metaData, object $entity): ?\Closure
    {
        if (!$metaData->idProperty?->isGeneratedValue) {
            return null;
        }

        return function (Storage $storage) use ($metaData, $entity) {
            $metaData->idProperty->reflection->setValue($entity, $storage->controller()->lastGeneratedValue());
            em()->resetKey($entity);
        };
    }

    private function prepareUpdate(object $entity, array $changedValues, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        foreach ($metaData->properties as $property) {
            if ($property->isModificationTimestamp) {
                $value = new \DateTime();
                $changedValues[$property->name] = $value;
                $property->reflection->setValue($entity, $value);
            }
        }

        $idValues = $this->valueGetter->get($entity, $metaData->idProperties);
        $this->dataSerializer->serialize($metaData, $idValues);
        $this->dataSerializer->serialize($metaData, $changedValues);

        $this->unitOfWorkManager->queueUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $changedValues,
            $idValues
        );

        $this->fetcher->updateRecord($metaData, $changedValues, $idValues);
    }

    public function prepareDelete(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        $idValues = $this->valueGetter->get($entity, $metaData->idProperties);
        $this->dataSerializer->serialize($metaData, $idValues);

        $this->unitOfWorkManager->queueDelete(
            $unitOfWork,
            $this->getStore($metaData),
            $idValues
        );

        $this->fetcher->removeRecord($metaData, $idValues);
    }
}
