<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

/**
 * Stands in for a database-generated id that is not known yet when the query is built:
 * the id of the entity's row in its id store, which its dependent (child-store) rows share.
 *
 * It reads the id from the entity itself, which the id-store insert sets on completion.
 * The unit of work runs dependent records after all records, so by then it is there,
 * however many entities the flush creates. "The last generated id" is not enough: with
 * two entities, both dependent rows would get the id of the second.
 */
readonly class LastInsertIdPlaceholder
{
    public function __construct(
        private object              $entity,
        private \ReflectionProperty $idProperty,
    )
    {
    }

    public function value(): mixed
    {
        return $this->idProperty->getValue($this->entity);
    }
}
