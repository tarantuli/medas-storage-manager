<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\Conditions\Condition;
use Medas\EntityManager\Selector\Conditions\WhereIs;
use Medas\EntityManager\Selector\Operants\Argument;
use Medas\EntityManager\Selector\Operants\Property;
use Medas\EntityManager\Selector\Parameter;
use Medas\EntityManager\Selector\Relations\Relation;
use Medas\EntityManager\Selector\Selector;
use Medas\EntityManager\Selector\Sorting\SortBy;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Cache\CacheManager;
use Medas\StorageManager\Databases\Pdo\Database;

#[Service]
class SelectQueryBuilder
{
    private string $query;
    private array $stores;
    private string $mainEntity;
    private Database $database;

    public function __construct(
        private CacheManager    $cacheManager,
        private MetaDataManager $metaDataManager,
    )
    {
    }

    public function build(Selector $selector, array $arguments): Query
    {
        /** @var ParaQuery $paraQuery */
        $paraQuery = $this->cacheManager->get()->get(
            [static::class, $selector::class],
            fn() => $this->process($selector)
        );

        return $this->compileQuery($paraQuery, $arguments);
    }

    private function process(Selector $selector): ParaQuery
    {
        $definition = $selector->get();
        $metaData = $this->metaDataManager->get($definition->entity);

        $this->database = storage($metaData->entity->storage);
        $this->mainEntity = $metaData->className;

        $quotedMainStore = $this->database->quote($metaData->entity->store);
        $this->stores = [$this->mainEntity => $quotedMainStore];

        $this->query = 'SELECT * FROM ' . $quotedMainStore;

        $this->processRelations($definition->relations);
        $this->processConditions($definition->conditions);
        $this->processSorting($definition->sorts);

        return new ParaQuery($this->query, $definition->parameters, $this->database);
    }

    /** @param Relation[] $relations */
    private function processRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            throw new \Exception('unhandled relation');
        }
    }

    /** @param Condition[] $conditions */
    private function processConditions(array $conditions): void
    {
        if ($conditions) {
            $this->query .= ' WHERE ';
        }
        foreach ($conditions as $condition) {
            if ($condition instanceof WhereIs) {
                $this->processComparison($condition, '=');
                continue;
            }
            throw new \Exception('unhandled condition');
        }
    }

    private function processComparison(WhereIs $condition, string $operator): void
    {
        if ($condition->property instanceof Property) {
            $property = $this->stores[$condition->property->entity ?? $this->mainEntity]
                . '.'
                . $this->database->quote($condition->property->name);
        }
        else {
            throw new \Exception('unhandled condition property type ' . $condition->property::class);
        }

        if ($condition->value instanceof Argument) {
            $value = ':' . $condition->value->name;
        }
        else {
            throw new \Exception('unhandled condition value  type ' . $condition->value::class);
        }

        $this->query .= $property . $operator . $value;
    }

    /** @param SortBy[] $sorts */
    private function processSorting(array $sorts): void
    {
        foreach ($sorts as $sort) {
            throw new \Exception('unhandled sort');
        }

    }

    private function compileQuery(ParaQuery $paraQuery, array $arguments): Query
    {
        $query = new Query($paraQuery->query, [], $paraQuery->database);

        /** @var Parameter $parameter */
        foreach ($paraQuery->parameters as $parameter) {
            if (isset($arguments[$parameter->name])) {
                $value = $arguments[$parameter->name];
            }
            elseif ($parameter->hasDefault) {
                $value = $parameter->default;
            }
            else {
                throw new \Exception('no value given for parameter ' . $parameter->name);
            }
            $query->arguments[$parameter->name] = $value;
        }

        return $query;
    }
}

