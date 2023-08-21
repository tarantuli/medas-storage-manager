<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\Attributes\Service;
use Medas\Core\Exceptions\GuidProviderIsNotAvailable;
use Medas\Core\Interfaces\GuidProvider;
use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\{Collection, Guid};
use Medas\StorageManager\Interfaces\{Storage, Store};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\EntityStructureFinder;
use Medas\StorageManager\UnitOfWork\{Priority, UnitOfWork, UnitOfWorkManager};

#[Service]
class EntityPersister
{
    public function __construct(
        private readonly DataSerializer        $dataSerializer,
        private readonly EntityStructureFinder $entityStructureFinder,
        private readonly GuidProvider|null     $guidProvider,
        private readonly MetaDataManager       $metaDataManager,
        private readonly RecordManager         $recordManager,
        private readonly StorageManager        $storageManager,
        private readonly UnitOfWorkManager     $unitOfWorkManager,
        private readonly ValueGetter           $valueGetter,
    )
    {
    }

    public function prepareCreate(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);
        $blueprint = $this->entityStructureFinder->find($entity::class);
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
                if ($property->type instanceof Collection) {
                    $this->queueCollectionUpdate($unitOfWork, $metaData, $entity, $property);
                }
                else {
                    $value = $property->reflection->getValue($entity);
                    $foundValue = true;
                }
            }
            elseif ($property->type instanceof Guid) {
                if ($this->guidProvider === null) {
                    throw new GuidProviderIsNotAvailable();
                }

                $value = $this->guidProvider->create();
                $property->reflection->setValue($entity, $value);
                $foundValue = true;
            }

            if ($foundValue) {
                $store = $blueprint->fieldByName($property->name)->store;
                if (!array_key_exists($store, $values)) {
                    $values[$store] = [];
                }

                $values[$store][$property->name] = $value;
            }
        }

        $idFieldStore = $blueprint->idField()->store;

        if (!array_key_exists($idFieldStore, $values)) {
            $values[$idFieldStore] = [];
        }

        foreach ($values as $store => $subValues) {
            $this->dataSerializer->serializeArray($metaData, $subValues);
            $priority = null;

            if ($store !== $idFieldStore) {
                $subValues[$blueprint->idField()->name] = new LastInsertIdPlaceholder();
                $priority = Priority::CreateDependentRecord;
            }

            $this->unitOfWorkManager->queueCreate(
                $unitOfWork,
                $this->storageManager->controller($metaData->entity->storage)->store($store),
                $subValues,
                $this->generatedValueSetter($metaData, $entity),
                $priority,
            );
        }
    }

    private function getStore(MetaData $metaData): Store
    {
        return $this->storageManager->controller($metaData->entity->storage)->store($metaData->entity->store);
    }

    private function generatedValueSetter(MetaData $metaData, object $entity): \Closure|null
    {
        if (!$metaData->idProperty) {
            return null;
        }

        return function (Storage $storage, int|null $lastInsertId) use ($metaData, $entity) {
            if ($metaData->idProperty->isGeneratedValue) {
                $value = $lastInsertId ?? $storage->controller()->lastGeneratedValue();
                $metaData->idProperty->reflection->setValue($entity, $value);
            }

            em()->resetKey($entity);
        };
    }

    public function prepareUpdate(object $entity, array $changedValues, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        foreach ($metaData->properties as $property) {
            if ($property->isModificationTimestamp) {
                $value = new \DateTime();
                $changedValues[$property->name] = $value;
                $property->reflection->setValue($entity, $value);
            }

            if ($property->type instanceof Collection) {
                $this->queueCollectionUpdate($unitOfWork, $metaData, $entity, $property);

                unset($changedValues[$property->name]);
            }
        }

        // Stop if there's no values left to update
        if ($changedValues === []) {
            return;
        }

        $this->dataSerializer->serializeArray($metaData, $changedValues);
        $idValues = $this->getIdValues($entity, $metaData);

        $this->unitOfWorkManager->queueUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $changedValues,
            $idValues
        );

        $this->recordManager->updateRecord($metaData, $changedValues, $idValues);
    }

    public function prepareDelete(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);
        $idValues = $this->getIdValues($entity, $metaData);

        $this->unitOfWorkManager->queueDelete(
            $unitOfWork,
            $this->getStore($metaData),
            $idValues
        );

        $this->recordManager->removeRecord($metaData, $idValues);
    }

    private function getIdValues(object $entity, MetaData $metaData): array
    {
        $idValue = $this->valueGetter->getValue($entity, $metaData->idProperty);
        $serializeValue = $this->dataSerializer->serializeValue($metaData, $metaData->idProperty, $idValue);

        return [$metaData->idProperty->name => $serializeValue];
    }

    private function queueCollectionUpdate(UnitOfWork $unitOfWork, MetaData $metaData, object $entity, MetaData\Property $property): void
    {
        if (!$property->type instanceof Collection) {
            throw new \Exception('property type should be a Collection instance');
        }

        $this->unitOfWorkManager->queueCollectionUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $entity,
            $property->name,
            $property->type,
            $property->reflection->getValue($entity)
        );
    }
}

