<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\{Binary, Boolean, Integer, Relation};
use Medas\ServiceManager\Attributes\Service;
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
            $field = new Blueprint\Field($property->name, Blueprint\Type::Text);
            $this->analyseProperty($property, $field);
            $this->blueprint->addField($field);
        }
    }

    protected function analyseProperty(MetaData\Property $property, Blueprint\Field $field): void
    {
        $typeHandler = $this->typeHandlerFinder->for($property->type);
        $field->type = $typeHandler->fieldType($property);

        if (!$property->isGeneratedValue && $property->isNullable) {
            $field->isNullable = true;
        }

        if ($property->isGeneratedValue) {
            $field->isGenerated = true;
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
            $field->maxValue = $type->minValue;
        }

        if ($type instanceof Binary) {
            $field->minLength = $type->minLength;
            $field->maxLength = $type->maxLength;
        }

        if ($type instanceof Boolean) {
            $field->minValue = 0;
            $field->maxValue = 1;
        }
    }

    private function findPrimaryKey(): void
    {
        $index = new Blueprint\Index([], true);

        foreach ($this->metaData->idProperties as $property) {
            $index->fields[] = $this->blueprint->fieldByName($property->name);
        }

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
}
