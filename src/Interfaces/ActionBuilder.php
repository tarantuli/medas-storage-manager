<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

use Medas\Core\Interfaces\ManagedCollection;
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Types\Collection;
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionCollection;

interface ActionBuilder
{
    public function createStore(Blueprint $blueprint): ActionCollection;

    public function fromSelector(Selector $selector, array $arguments): ActionCollection;

    public function prepareCreate(Store $store, array $values): ActionCollection;

    public function prepareGet(Store $store, array $filters): ActionCollection;

    public function prepareUpdate(Store $store, array $updates, array $conditions): ActionCollection;

    public function prepareCollectionUpdate(Store $store, object $entity, string $name, Collection $type, ManagedCollection $values): ActionCollection;

    public function prepareDelete(Store $store, array $conditions): ActionCollection;

}
