<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\Relation;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Structure\{TypeHandlerFinder};
use Medas\StorageManager\Structure\Blueprint\{ForeignKey, Type};

#[Service]
class RelationHandler extends BaseHandler
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
    )
    {
    }

    public function fieldType(Property $property): Type
    {
        // We can't inject it in the constructor due to circular dependencies
        $typeHandlerFinder = service(TypeHandlerFinder::class);
        $idProperty = $this->getIdProperty($property);

        return $typeHandlerFinder->for($idProperty->type)->fieldType($property);
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
