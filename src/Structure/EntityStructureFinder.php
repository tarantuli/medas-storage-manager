<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\CacheManager;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Properties\Handler;
use Medas\EntityManager\Types\{Binary, Boolean, Collection, Integer, Relation};
use Medas\StorageManager\Structure\Blueprint\Type;
use Medas\StorageManager\Structure\TypeHandlers\{EnumHandler, RelationHandler};

#[Service]
readonly class EntityStructureFinder
{
    public function __construct(
        private CacheManager      $cacheManager,
        private EnumHandler       $enumHandler,
        private MetaDataManager   $metaDataManager,
        private ParentStoreFinder $parentStoreFinder,
        private TypeHandlerFinder $typeHandlerFinder,
    )
    {
    }

    public function find(string $className): Blueprint
    {
        return $this->cacheManager->get()->get(
            [__CLASS__, $className],
            fn() => $this->compile($className)
        );
    }

    private function compile(string $className): Blueprint
    {
        $job = new EntityStructureFinder\Job(
            $this->metaDataManager->get($className),
            new Blueprint()
        );

        $this->findName($job);
        $this->findInheritance($job);
        $this->findFields($job);
        $this->findPrimaryKey($job);
        $this->findKeys($job);
        $this->findForeignKeys($job);

        return $job->blueprint;
    }

    private function findName(EntityStructureFinder\Job $job): void
    {
        if ($job->metaData->entity->store === null) {
            return;
        }

        $job->blueprint->name = $job->metaData->entity->store;
        $job->blueprint->parent = $job->metaData->inheritance->parent;
    }

    private function findInheritance(EntityStructureFinder\Job $job): void
    {
        if ($job->blueprint->storeOriginalClass = $job->metaData->inheritance->storeOriginalClass) {
            $job->blueprint->storeRequestingOriginalClassStorage =
                $this->metaDataManager->get($job->metaData->inheritance->sharedParentClass)->entity->store;
        }
    }

    private function findFields(EntityStructureFinder\Job $job): void
    {
        $parents = $this->parentStoreFinder->find($job->metaData);

        foreach ($job->metaData->properties as $property) {
            $job->blueprint->addField(
                $this->fieldFromProperty($property, $parents)
            );
        }
    }

    public function fieldFromProperty(MetaData\Property $property, ParentStores $parentStores = null): Blueprint\Field
    {
        $field = new Blueprint\Field($property->name, Blueprint\Type::Text);

        if ($parentStores) {
            $field->store = $parentStores->getStore($property->reflection->getDeclaringClass()->name);
        }

        $typeHandler = $this->typeHandlerFinder->for($property->type);
        $field->type = $typeHandler->fieldType($property);

        if ($field->type === Type::Collection) {
            $this->handleCollectionField($property, $field);
        }

        if (!$property->isGeneratedValue && $property->isNullable) {
            $field->isNullable = true;
        }

        if ($property->isGeneratedValue) {
            $field->isGenerated = true;
        }

        if ($property->isCreationTimestamp) {
            $field->isCreationTimestamp = true;
        }

        if ($property->isModificationTimestamp) {
            $field->isModificationTimestamp = true;
        }

        if ($property->hasDefault) {
            $field->hasDefault = true;
            $field->default = $property->default;

            if ($class = $property->handler) {
                // This property has been assigned a handler, let it serialize the value
                /** @var Handler $propertyHandler */
                $propertyHandler = service($class);
                $field->default = $propertyHandler->serialize($field->default);
            }
        }

        $type = $property->type;

        if ($type instanceof Relation) {
            if (enum_exists($type->entity)) {
                $type = $this->enumHandler->getPseudoType($type->entity);
            }
            else {
                // Use the type of the id property of the related entity
                /** @var RelationHandler $typeHandler */
                $type = $typeHandler->getIdProperty($type->entity)->type;
            }
        }

        if ($type instanceof Integer) {
            $field->minValue = $type->minValue;
            $field->maxValue = $type->maxValue;
        }

        if ($type instanceof Binary) {
            $field->minLength = $type->minLength;
            $field->maxLength = $type->maxLength;
        }

        if ($type instanceof Boolean) {
            $field->minValue = 0;
            $field->maxValue = 1;
        }

        return $field;
    }

    private function findPrimaryKey(EntityStructureFinder\Job $job): void
    {
        $index = new Blueprint\Index([], true);
        $index->addField($job->blueprint->fieldByName($job->metaData->idProperty->name));

        $job->blueprint->addIndex($index);
    }

    private function findKeys(EntityStructureFinder\Job $job): void
    {
        // Unique values
        foreach ($job->metaData->properties as $property) {
            if (!$property->isUnique) {
                continue;
            }

            $job->blueprint->addIndex(
                new Blueprint\Index([$job->blueprint->fieldByName($property->name)], false, true)
            );
        }
    }

    private function findForeignKeys(EntityStructureFinder\Job $job): void
    {
        foreach ($job->metaData->properties as $property) {
            $handler = $this->typeHandlerFinder->for($property->type);

            if ($foreignKey = $handler->foreignKey($property)) {
                $job->blueprint->addForeignKey($foreignKey);
            }
        }
    }

    private function handleCollectionField(MetaData\Property $property, Blueprint\Field $field): void
    {
        /** @var Collection $collectionType */
        $collectionType = $property->type;
        $collectionTypeHandler = $this->typeHandlerFinder->forString($collectionType->contentType);

        if (!$collectionTypeHandler instanceof RelationHandler) {
            return;
        }

        $referencedMetaData = $this->metaDataManager->get($collectionType->contentType);

        $collectionProperty = $referencedMetaData->idProperty;

        $field->collectionField = $this->fieldFromProperty($collectionProperty);
        $field->collectionStore = $referencedMetaData->entity->store;
    }
}
