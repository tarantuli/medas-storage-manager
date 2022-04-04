<?php

declare(strict_types=1);

namespace Medas\Test\MockUps\Selectors;

use Medas\EntityManager\Selector\{Conditions\WhereIs,
    Definition,
    Operants\Argument,
    Operants\Property,
    Parameter,
    Selector
};
use Medas\ServiceManager\AsSingleton;
use Medas\Test\MockUps\Migrations\StoredEntity;

class StoredEntityWithName implements Selector
{
    use AsSingleton;

    public function get(): Definition
    {
        return Definition::create($this->entity())
            ->add(new Parameter('name'))
            ->add(new WhereIs(new Property('name'), new Argument('name')));
    }

    public function entity(): string
    {
        return StoredEntity::class;
    }
}
