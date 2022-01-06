<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\Relation;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Structure\Blueprint\ForeignKey;
use Medas\StorageManager\Databases\Pdo\Structure\TypeHandlerFactory;

#[Service]
class RelationHandler extends BaseHandler
{
    public function __construct(
        private MetaDataManager $metaDataManager,
    )
    {
    }

    public function fieldDefinition(Property $property): string
    {
        // We can't inject it in the constructor due to circular dependencies
        $typeHandlerFactory = service(TypeHandlerFactory::class);
        $idProperty = $this->getIdProperty($property);

        return $typeHandlerFactory->for($idProperty->type)->fieldDefinition($idProperty);
    }

    private function getIdProperty(Property $property): ?Property
    {
        /** @var Relation $type */
        $type = $property->type;
        $metaData = $this->metaDataManager->get($type->entity);

        return $metaData->idProperty;
    }

    public function foreignKey(Property $property): ForeignKey|null
    {
        /** @var Relation $type */
        $type = $property->type;
        $metaData = $this->metaDataManager->get($type->entity);

        $idProperty = $this->getIdProperty($property);

        return new ForeignKey($property->name, $metaData->entity->store, $idProperty->name);
    }
}
