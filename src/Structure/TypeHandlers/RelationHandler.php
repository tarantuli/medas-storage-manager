<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\TypeHandlers;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\{MetaData\Property, MetaDataManager, Types\Relation};
use Medas\StorageManager\Structure\{Blueprint\ForeignKey, Blueprint\Type, TypeHandlerFinder};

#[Service]
readonly class RelationHandler extends BaseHandler
{
    public function __construct(
        private MetaDataManager $metaDataManager,
        private EnumHandler     $enumHandler,
    )
    {
    }

    public function fieldType(Property|null $property): Type
    {
        /** @var Relation $type */
        $type = $property->type;

        return match (true) {
            enum_exists($type->entity) => $this->enumHandler->getBlueprintType($type->entity),
            default => $this->getEntityType($type->entity),
        };
    }

    public function getIdProperty(string $entity): Property|null
    {
        return $this->metaDataManager->get($entity)->idProperty;
    }

    public function foreignKey(Property $property): ForeignKey|null
    {
        /** @var Relation $type */
        $type = $property->type;

        if (enum_exists($type->entity)) {
            return null;
        }

        return new ForeignKey(
            $property->name,
            $this->metaDataManager->get($type->entity)->entity->store,
            $this->getIdProperty($type->entity)->name,
            $property->onDeleteCascade,
        );
    }

    private function getEntityType(string $entity): Type
    {
        $idProperty = $this->getIdProperty($entity);

        // We can't inject it in the constructor due to circular dependencies
        $typeHandlerFinder = service(TypeHandlerFinder::class);

        return $typeHandlerFinder->for($idProperty->type)->fieldType($idProperty);
    }
}
