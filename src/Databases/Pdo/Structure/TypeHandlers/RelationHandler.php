<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure\TypeHandlers;

use Medas\EntityManager\MetaData\Property;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Types\Relation;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Databases\Pdo\Structure\TypeHandlerFactory;

#[Service]
class RelationHandler implements TypeHandler
{
    public function __construct(
        private MetaDataManager    $metaDataManager,
        private TypeHandlerFactory $typeHandlerFactory,
    )
    {
    }

    public function fieldType(Property $property): string
    {
        /** @var Relation $type */
        $type = $property->type;

        $metaData = $this->metaDataManager->get($type->className);
        $idProperty = $metaData->idProperty;

        return $this->typeHandlerFactory->for($idProperty->type)->fieldType($idProperty);
    }
}
