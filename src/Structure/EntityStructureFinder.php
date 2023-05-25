<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\{Binary, Boolean, Collection, Integer, Relation};
use Medas\StorageManager\Structure\Blueprint\Type;
use Medas\StorageManager\Structure\TypeHandlers\{EnumHandler, RelationHandler};

#[Service]
class EntityStructureFinder
{
    private MetaData $metaData;
    private Blueprint $blueprint;

    public function __construct(
        private readonly MetaDataManager   $metaDataManager,
        private readonly TypeHandlerFinder $typeHandlerFinder,
        private readonly EnumHandler       $enumHandler,
    )
    {
    }

    public function find(string $className): Blueprint
    {
        $this->metaData = $this->metaDataManager->get($className);
        $this->blueprint = new Blueprint();
        $this->findName();
        $this->findFields();
        $this->findPrimaryKey();
        $this->findKeys();
        $this->findForeignKeys();

        return $this->blueprint;
    }

    private function findName(): void
    {
        $this->blueprint->setName($this->metaData->entity->store);
    }

    private function findFields(): void
    {
        foreach ($this->metaData->properties as $property) {
            $this->blueprint->addField(
                $this->fieldFromProperty($property)
            );
        }
    }

    public function fieldFromProperty(MetaData\Property $property): Blueprint\Field
    {
        $field = new Blueprint\Field($property->name, Blueprint\Type::Text);

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

    private function findPrimaryKey(): void
    {
        $index = new Blueprint\Index([], true);
        $index->addField($this->blueprint->fieldByName($this->metaData->idProperty->name));

        $this->blueprint->addIndex($index);
    }

    private function findKeys(): void
    {
        // Unique values
        foreach ($this->metaData->properties as $property) {
            if (!$property->isUnique) {
                continue;
            }

            $this->blueprint->addIndex(
                new Blueprint\Index([$this->blueprint->fieldByName($property->name)], false, true)
            );
        }
    }

    private function findForeignKeys(): void
    {
        foreach ($this->metaData->properties as $property) {
            $handler = $this->typeHandlerFinder->for($property->type);

            if ($foreignKey = $handler->foreignKey($property)) {
                $this->blueprint->addForeignKey($foreignKey);
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

        $collectionProperty = $this->metaDataManager
            ->get($collectionType->contentType)
            ->idProperty;

        $referencedMetaData = $this->metaDataManager->get($collectionProperty->reflection->class);

        $field->collectionField = $this->fieldFromProperty($collectionProperty);
        $field->collectionStore = $referencedMetaData->entity->store;
    }
}
