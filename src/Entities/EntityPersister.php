<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    Exceptions\GuidProviderIsNotAvailable,
    Interfaces\GuidProvider,
    Interfaces\TracksChanges
};
use Medas\EntityManager\{
    Hydration\ValueGetter,
    MetaData,
    MetaDataManager,
    Snapshots\PropertyChange,
    Types\Collection,
    Types\Guid
};
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\DefaultStrategy;
use Medas\StorageManager\Inheritance\OriginalClassStorageStrategy;
use Medas\StorageManager\Interfaces\{Storage, Store};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\Structure\EntityStructureFinder;
use Medas\StorageManager\UnitOfWork\{Priority, UnitOfWork, UnitOfWorkManager};

#[Service]
readonly class EntityPersister
{
    public function __construct(
        private DataSerializer               $dataSerializer,
        private EntityStructureFinder        $entityStructureFinder,
        private GuidProvider|null            $guidProvider,
        private MetaDataManager              $metaDataManager,
        private StoreRecordManager           $recordManager,
        private StorageManager               $storageManager,
        private UnitOfWorkManager            $unitOfWorkManager,
        private ValueGetter                  $valueGetter,

        #[ConfigValue(DefaultStrategy::class)]
        private OriginalClassStorageStrategy $originalClassStorageStrategy,
    )
    {
    }

    public function prepareCreate(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);
        $blueprint = $this->entityStructureFinder->find($entity::class);
        $valuesPerStore = [];

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

                if (!array_key_exists($store, $valuesPerStore)) {
                    $valuesPerStore[$store] = [];
                }

                $valuesPerStore[$store][$property->name] = $value;
            }
        }

        $idFieldStore = $blueprint->idField()->store;

        if (!array_key_exists($idFieldStore, $valuesPerStore)) {
            $valuesPerStore[$idFieldStore] = [];
        }

        foreach ($valuesPerStore as &$subValues) {
            $this->dataSerializer->serializeArray($metaData, $subValues);
        }

        if ($blueprint->storeOriginalClass) {
            $values = $this->originalClassStorageStrategy->createValuesToStore($blueprint, $entity);
            $valuesPerStore = array_merge_recursive($valuesPerStore, $values);
        }

        foreach ($valuesPerStore as $store => $values) {
            $priority = null;

            if ($store !== $idFieldStore) {
                $values[$blueprint->idField()->name] = new LastInsertIdPlaceholder();
                $priority = Priority::CreateDependentRecord;
            }

            $this->unitOfWorkManager->queueCreate(
                $unitOfWork,
                $this->storageManager->controller($metaData->entity->storage)->store($store),
                $values,
                $this->generatedValueSetter($metaData, $entity),
                $priority,
            );
        }
    }

    private function generatedValueSetter(MetaData $metaData, object $entity): \Closure|null
    {
        if (!$metaData->idProperty) {
            return null;
        }

        return function (Storage $storage, mixed $lastInsertId) use ($metaData, $entity) {
            if ($metaData->idProperty->isGeneratedValue) {
                $value = $lastInsertId ?? $this->storageManager->controller($storage)->lastGeneratedValue();

                $metaData->idProperty->reflection->setValue($entity, $value);
            }

            em()->resetKey($entity);
        };
    }

    /** @param PropertyChange[] $changedValues */
    public function prepareUpdate(object $entity, array $changedValues, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);

        // Replace the PropertyChange objects by the new values
        foreach ($changedValues as &$changedValue) {
            $changedValue = $changedValue->current;
        }

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

    private function queueCollectionUpdate(
        UnitOfWork        $unitOfWork,
        MetaData          $metaData,
        object            $entity,
        MetaData\Property $property
    ): void
    {
        if (!$property->type instanceof Collection) {
            throw new \Exception('property type should be a Collection instance');
        }

        $value = $property->reflection->getValue($entity);

        $this->unitOfWorkManager->queueCollectionUpdate(
            $unitOfWork,
            $this->getStore($metaData),
            $entity,
            $property->name,
            $property->type,
            $value
        );

        if ($value instanceof TracksChanges) {
            $value->resetChangeTracking();
        }
    }

    public function prepareDelete(object $entity, UnitOfWork $unitOfWork): void
    {
        $metaData = $this->metaDataManager->get($entity::class);
        $idValues = $this->getIdValues($entity, $metaData);

        $this->unitOfWorkManager->queueDelete($unitOfWork, $this->getStore($metaData), $idValues);
        $this->recordManager->removeRecord($metaData, $idValues);
    }

    private function getIdValues(object $entity, MetaData $metaData): array
    {
        $idValue = $this->valueGetter->getValue($entity, $metaData->idProperty);

        $serializeValue = $this->dataSerializer->serializeValue(
            $metaData,
            $metaData->idProperty,
            $idValue
        );

        return [$metaData->idProperty->name => $serializeValue];
    }

    private function getStore(MetaData $metaData): Store
    {
        return $this->storageManager->controller($metaData->entity->storage)->store($metaData->entity->store);
    }
}
