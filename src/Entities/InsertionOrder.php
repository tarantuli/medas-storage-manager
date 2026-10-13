<?php

declare(strict_types=1);

namespace Medas\StorageManager\Entities;

use Medas\Core\{Attributes\Service, Types\Collection};
use Medas\EntityManager\MetaDataManager;
use Medas\StorageManager\Exceptions\ReferenceCycleBetweenNewEntities;

/**
 * Orders new entities so that each one comes after the new entities it refers to. The
 * unit of work inserts rows in the order they were queued, and the database checks a
 * foreign key as the row goes in, so a row must not arrive before the row it refers to.
 *
 * Only references between the new entities themselves count: an existing entity's row
 * is already there. Collections are left out, because their join rows are written after
 * all records are created, and back-references hold no foreign key in this row.
 */
#[Service]
readonly class InsertionOrder
{
    public function __construct(
        private MetaDataManager $metaDataManager,
    )
    {
    }

    /**
     * @param object[] $entities In the order they were persisted
     *
     * @return object[] The same entities; persist order is kept wherever references allow it
     *
     * @throws ReferenceCycleBetweenNewEntities
     */
    public function sort(array $entities): array
    {
        $entities = array_values($entities);

        if (count($entities) < 2) {
            return $entities;
        }

        $positions = [];

        foreach ($entities as $position => $entity) {
            $positions[spl_object_id($entity)] = $position;
        }

        // For each position, the positions that may only follow it, and for each position
        // the number of positions it still waits for.
        $followers = array_fill(0, count($entities), []);
        $waitingFor = array_fill(0, count($entities), 0);

        foreach ($entities as $position => $entity) {
            foreach ($this->referencedPositions($entity, $positions) as $referencedPosition) {
                // A row may refer to itself: it is checked as it goes in, so it is there.
                if ($referencedPosition !== $position) {
                    $followers[$referencedPosition][] = $position;

                    $waitingFor[$position]++;
                }
            }
        }

        // Kahn's algorithm, always taking the earliest persisted entity that is ready, so
        // entities persisted in a workable order keep exactly that order.
        $ready = new \SplMinHeap();

        foreach ($waitingFor as $position => $count) {
            if ($count === 0) {
                $ready->insert($position);
            }
        }

        $sorted = [];

        while (!$ready->isEmpty()) {
            $position = $ready->extract();
            $sorted[] = $entities[$position];

            foreach ($followers[$position] as $follower) {
                if (--$waitingFor[$follower] === 0) {
                    $ready->insert($follower);
                }
            }
        }

        if (count($sorted) < count($entities)) {
            $notInserted = [];

            foreach ($waitingFor as $position => $count) {
                if ($count > 0) {
                    $notInserted[] = $entities[$position]::class;
                }
            }

            throw new ReferenceCycleBetweenNewEntities($notInserted);
        }

        return $sorted;
    }

    /**
     * @param array<int, int> $positions Positions of the new entities, by object id
     *
     * @return int[]
     */
    private function referencedPositions(object $entity, array $positions): array
    {
        $referencedPositions = [];

        foreach ($this->metaDataManager->get($entity::class)->properties as $property) {
            if ($property->type instanceof Collection || !$property->reflection->isInitialized($entity)) {
                continue;
            }

            $value = $property->reflection->getValue($entity);

            if (is_object($value) && isset($positions[spl_object_id($value)])) {
                $referencedPositions[] = $positions[spl_object_id($value)];
            }
        }

        return $referencedPositions;
    }
}
