<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Selectors;

use Medas\EntityManager\Selector\{Conditions\WhereIs,
    Definition,
    Operants\Argument,
    Operants\Property,
    Parameter,
    Selector
};
use Medas\ServiceManager\AsSingleton;
use Medas\StorageManagerTest\MockUps\Migrations\StoredEntity;

class StoredEntityWithId implements Selector
{
    use AsSingleton;

    public function get(): Definition
    {
        return Definition::create($this->entity())
            ->add(new Parameter('id'))
            ->add(new WhereIs(new Property('id'), new Argument('id')));
    }

    public function entity(): string
    {
        return StoredEntity::class;
    }
}
