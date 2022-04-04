<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\Condition,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Exceptions\UndeclaredParametersException,
    Exceptions\UnhandledConditionTypeException,
    Exceptions\UnhandledRelationTypeException,
    Exceptions\UnhandledSortTypeException,
    Operants\Argument,
    Operants\Property,
    Operants\Value,
    Parameter,
    Relations\Relation,
    Selector,
    Sorting\SortBy
};
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Cache\CacheManager;
use Medas\ServiceManager\Interfaces\NotCacheable;
use Medas\StorageManager\Databases\Pdo\Database;

#[Service]
class SelectQueryBuilder
{
    private string $query;
    private array $stores;
    private array $foundArguments;
    private array $foundConstants;
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
        if ($selector instanceof NotCacheable) {
            $paraQuery = $this->process($selector);
        }
        else {
            /** @var ParaQuery $paraQuery */
            $paraQuery = $this->cacheManager->get()->get(
                [static::class, $selector::class],
                fn() => $this->process($selector)
            );
        }

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
        $this->foundArguments = [];
        $this->foundConstants = [];

        $this->query = 'SELECT * FROM ' . $quotedMainStore;

        $this->processRelations($definition->relations);
        $this->processConditions($definition->conditions);
        $this->processSorting($definition->sorts);
        $this->processParameters($definition->parameters);

        return new ParaQuery($this->query, $definition->parameters, $this->foundConstants, $this->database);
    }

    /** @param Relation[] $relations */
    private function processRelations(array $relations): void
    {
        foreach ($relations as $relation) {
            throw new UnhandledRelationTypeException($relation);
        }
    }

    /** @param Condition[] $conditions */
    private function processConditions(array $conditions): void
    {
        if ($conditions) {
            $this->query .= ' WHERE ';
        }
        foreach ($conditions as $condition) {
            match ($condition::class) {
                WhereIs::class => $this->processComparison($condition, '='),
                WhereIsMoreThan::class => $this->processComparison($condition, '>'),
                WhereIsLessThan::class => $this->processComparison($condition, '<'),
                WhereIsAtLeast::class => $this->processComparison($condition, '>='),
                WhereIsAtMost::class => $this->processComparison($condition, '<='),
                default => throw new UnhandledConditionTypeException($condition),
            };
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
            $this->foundArguments[$condition->value->name] = true;
        }
        elseif ($condition->value instanceof Value) {
            $name = sha1(serialize($condition->value->value));
            $value = ':' . $name;
            $this->foundConstants[$name] = $condition->value->value;
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
            throw new UnhandledSortTypeException($sort);
        }

    }

    private function processParameters(array $parameters): void
    {
        /** @var Parameter $parameter */
        foreach ($parameters as $parameter) {
            unset($this->foundArguments[$parameter->name]);
        }

        if ($this->foundArguments) {
            throw new UndeclaredParametersException(array_keys($this->foundArguments));
        }
    }

    private function compileQuery(ParaQuery $paraQuery, array $arguments): Query
    {
        $query = new Query($paraQuery->query, $paraQuery->constants, $paraQuery->database);

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

