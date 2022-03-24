<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

use Medas\EntityManager\MetaData;
use Medas\EntityManager\MetaDataManager;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Field;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\Index;

#[Service]
class EntityStructureFinder
{
    public function __construct(
        private MetaDataManager   $metaDataManager,
        private TypeHandlerFinder $typeHandlerFinder,
    )
    {
    }

    public function find(string $className): Blueprint
    {
        $metaData = $this->metaDataManager->get($className);
        $blueprint = new Blueprint();

        $this->findName($metaData, $blueprint);
        $this->findFields($metaData, $blueprint);
        $this->findPrimaryKey($metaData, $blueprint);
        $this->findKeys($metaData, $blueprint);
        $this->findForeignKeys($metaData, $blueprint);

        return $blueprint;
    }

    private function findName(MetaData $metaData, Blueprint $blueprint): void
    {
        $blueprint->name = $metaData->entity->store;
    }

    private function findFields(MetaData $metaData, Blueprint $blueprint): void
    {
        foreach ($metaData->properties as $property) {
            $definition = $this->determineDefinition($property);

            $blueprint->addField(new Field($property->name, $definition));
        }
    }

    private function determineDefinition(MetaData\Property $property): string
    {
        $handler = $this->typeHandlerFinder->for($property->type);
        $definition = $handler->fieldDefinition($property);

        if ($property->isGeneratedValue) {
            $definition .= ' NOT NULL AUTO_INCREMENT';
        }
        elseif ($property->isNullable) {
            if ($property->default !== null) {
                $definition .= $this->getDefaultDefinition($property);
            }
            else {
                $definition .= ' DEFAULT NULL';
            }
        }
        else {
            $definition .= ' NOT NULL';
            if ($property->default !== null) {
                $definition .= $this->getDefaultDefinition($property);
            }
        }

        return $definition;
    }

    private function getDefaultDefinition(MetaData\Property $property): string
    {
        $default = $property->default;

        if (is_bool($default)) {
            $default = (int) $default;
        }

        return ' DEFAULT ' . $default;
    }

    private function findPrimaryKey(MetaData $metaData, Blueprint $blueprint): void
    {
        $index = new Index('PRIMARY');

        foreach ($metaData->idProperties as $property) {
            $index->fields[] = $blueprint->field($property->name);
        }

        $index->isUnique = true;
        $blueprint->addIndex($index);
    }

    private function findKeys(MetaData $metaData, Blueprint $blueprint): void
    {
        // Unique values
        foreach ($metaData->properties as $property) {
            if (!$property->isUnique) {
                continue;
            }

            $index = new Index($property->name);
            $index->fields[] = $blueprint->field($property->name);
            $index->isUnique = true;
            $blueprint->addIndex($index);
        }
    }

    private function findForeignKeys(MetaData $metaData, Blueprint $blueprint): void
    {
        foreach ($metaData->properties as $property) {
            $handler = $this->typeHandlerFinder->for($property->type);
            if ($foreignKey = $handler->foreignKey($property)) {
                $blueprint->addForeignKey($foreignKey);
            }
        }
    }
}
