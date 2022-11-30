<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Snapshots\{Snapshot, SnapshotManager};
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\{Storage, Store};
use Medas\StorageManager\UnitOfWork\{UnitOfWork, UnitOfWorkManager};

#[Service]
class Persister
{
    public function __construct(
        private readonly Fetcher           $fetcher,
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
        $serializer = storage($metaData->entity->storage)->controller()->serializer();
        $serializedValues = [];

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

            if ($foundValue) {
                $serializedValues[$property->name] = $serializer->serialize($property->type, $value);
            }
        }

        $this->unitOfWorkManager->queueCreate(
            $unitOfWork,
            $this->getStore($metaData),
            $serializedValues,
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

        $serializer = storage($metaData->entity->storage)->controller()->serializer();
        $serializedValues = [];

        foreach ($metaData->properties as $property) {
            if ($property->isModificationTimestamp) {
                $value = new \DateTime();
                $serializedValues[$property->name] = $serializer->serialize($property->type, $value);
                $property->reflection->setValue($entity, $value);
            }
        }

        foreach ($changedValues as $name => $value) {
            $serializedValues[$name] = $serializer->serialize($metaData->property($name)->type, $value);
        }

        $idValues = $this->valueGetter->get($entity, $metaData->idProperties);

        $this->unitOfWorkManager->queueUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $serializedValues,
            $idValues
        );

        $this->fetcher->updateRecord($metaData, $serializedValues, $idValues);
    }

    public function prepareDelete(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        $idValues = $this->valueGetter->get($entity, $metaData->idProperties);

        $this->unitOfWorkManager->queueDelete(
            $unitOfWork,
            $this->getStore($metaData),
            $idValues
        );

        $this->fetcher->removeRecord($metaData, $idValues);
    }
}
