<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Snapshots\Snapshot;
use Medas\EntityManager\Snapshots\SnapshotManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\UnitOfWork\UnitOfWork;
use Medas\StorageManager\UnitOfWork\UnitOfWorkManager;

#[Service]
class Persister
{
    public function __construct(
        private Fetcher           $fetcher,
        private MetaDataManager   $metaDataManager,
        private SnapshotManager   $snapshotManager,
        private UnitOfWorkManager $unitOfWorkManager,
        private ValueGetter       $valueGetter,
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
        $serializerFinder = storage($metaData->entity->storage)->getTypeSerializerFinder();
        $serializedValues = [];

        foreach ($metaData->properties as $property) {
            if ($property->reflection->isInitialized($entity)) {
                $value = $property->reflection->getValue($entity);
                $serializedValues[$property->name] = $serializerFinder->for($property->type)->serialize($value);
            }
        }

        $onComplete = $this->generatedValueSetter($metaData, $entity);

        $this->unitOfWorkManager->queueCreate(
            $unitOfWork,
            $this->getStore($metaData),
            $serializedValues,
            $onComplete
        );
    }

    private function generatedValueSetter(MetaData $metaData, object $entity): ?\Closure
    {
        if (!$metaData->idProperty?->isGeneratedValue) {
            return null;
        }

        return function (Storage $storage) use ($metaData, $entity) {
            $metaData->idProperty->reflection->setValue($entity, $storage->lastGeneratedValue());
            em()->resetKey($entity);
        };
    }

    private function getStore(MetaData $metaData): Store
    {
        return storage($metaData->entity->storage)->store($metaData->entity->store);
    }

    private function prepareUpdate(object $entity, array $changedValues, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        $serializerFinder = storage($metaData->entity->storage)->getTypeSerializerFinder();
        $serializedValues = [];
        foreach ($changedValues as $name => $value) {
            $serializedValues[$name] = $serializerFinder->for($metaData->property($name)->type)->serialize($value);
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
