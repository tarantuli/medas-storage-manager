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
        $serializedValues = [];

        foreach ($metaData->properties as $property) {
            if ($property->reflection->isInitialized($entity)) {
                $value = $property->reflection->getValue($entity);
                $serializedValues[$property->name] = $property->type->serialize($value);
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
        $serializedValues = [];

        foreach ($changedValues as $name => $value) {
            $property = $metaData->property($name);
            $serializedValues[$name] = $property->type->serialize($value);
        }

        $this->unitOfWorkManager->queueUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $serializedValues,
            $this->valueGetter->get($entity, $metaData->idProperties)
        );
    }
}
