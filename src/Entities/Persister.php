<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\EntityManager\Hydration\ValueGetter;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\Guid;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Exceptions\GuidProviderIsNotAvailable;
use Medas\ServiceManager\Interfaces\GuidProvider;
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
        private readonly UnitOfWorkManager $unitOfWorkManager,
        private readonly ValueGetter       $valueGetter,
    )
    {
    }

    public function prepareCreate(object $entity, UnitOfWork $unitOfWork): void
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
                    throw new GuidProviderIsNotAvailable();
                }

                $value = $this->guidProvider->create();
                $property->reflection->setValue($entity, $value);
                $foundValue = true;
            }

            if ($foundValue) {
                $values[$property->name] = $value;
            }
        }

        $this->dataSerializer->serializeArray($metaData, $values);

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
        if (!$metaData->idProperty) {
            return null;
        }

        return function (Storage $storage) use ($metaData, $entity) {
            if ($metaData->idProperty->isGeneratedValue) {
                $metaData->idProperty->reflection->setValue($entity, $storage->controller()->lastGeneratedValue());
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
        }

        $this->dataSerializer->serializeArray($metaData, $changedValues);
        $idValues = $this->getIdValues($entity, $metaData);

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
        $idValues = $this->getIdValues($entity, $metaData);

        $this->unitOfWorkManager->queueDelete(
            $unitOfWork,
            $this->getStore($metaData),
            $idValues
        );

        $this->fetcher->removeRecord($metaData, $idValues);
    }

    private function getIdValues(object $entity, MetaData $metaData): array
    {
        $idValue = $this->valueGetter->getValue($entity, $metaData->idProperty);
        $serializeValue = $this->dataSerializer->serializeDatum($metaData, $idValue);

        return [$metaData->idProperty->name => $serializeValue];
    }
}
