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
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Exceptions\UndeclaredParametersException,
    Exceptions\UnhandledConditionTypeException,
    Exceptions\UnhandledRelationTypeException,
    Exceptions\UnhandledSortTypeException,
    Operants\Argument,
    Operants\Operant,
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
                WhereIsNull::class => $this->processNullComparison($condition, true),
                WhereIsNotNull::class => $this->processNullComparison($condition, false),
                default => throw new UnhandledConditionTypeException($condition),
            };
        }
    }

    private function processComparison(WhereIs $condition, string $operator): void
    {
        $this->query .= $this->operantToQuery($condition->property) . $operator . $this->operantToQuery($condition->value);
    }

    private function operantToQuery(Operant $operant): string
    {
        if ($operant instanceof Property) {
            return $this->stores[$operant->entity ?? $this->mainEntity]
                . '.'
                . $this->database->quote($operant->name);
        }

        if ($operant instanceof Argument) {
            $this->foundArguments[$operant->name] = true;
            return ':' . $operant->name;
        }

        if ($operant instanceof Value) {
            $name = sha1(serialize($operant->value));
            $this->foundConstants[$name] = $operant->value;
            return ':' . $name;
        }
        throw new \Exception('unhandled operant type ' . $operant::class);
    }

    private function processNullComparison(WhereIsNull $condition, bool $isNull): void
    {
        $this->query .= $this->operantToQuery($condition->property) . ($isNull ? ' IS NULL' : ' IS NOT NULL');
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

