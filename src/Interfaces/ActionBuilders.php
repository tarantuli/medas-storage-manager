<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

/**
 * This should be a service that returns services
 */
interface ActionBuilders
{
    public function createStore(): Builders\CreateStoreBuilder;

    public function deleteStore(): Builders\DeleteStoreBuilder;

    public function selectorAction(): Builders\SelectorActionBuilder;

    public function insert(): Builders\InsertBuilder;

    public function get(): Builders\GetBuilder;

    public function update(): Builders\UpdateBuilder;

    public function delete(): Builders\DeleteBuilder;

    public function collectionUpdate(): Builders\CollectionUpdateBuilder;
}
