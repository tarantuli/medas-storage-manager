<?php

declare(strict_types=1);

namespace Medas\StorageManager\Sequences;

use Medas\StorageManager\Interfaces\Storage;

/**
 * Driver-specific primitive behind SequenceGenerator, one per storage type.
 * SequenceGenerator asks each supplier whether it canHandle() a given storage
 * and delegates to the first that does - so a project can run several storage
 * types side by side, each with its own sequence implementation.
 *
 * next() atomically claims and returns the next value in the (scope, year)
 * sequence held in $table on $storage. Implementations MUST make that value
 * unique across concurrent callers - the guarantee max-then-increment can't
 * make - typically via a single row-locking statement rather than a read
 * followed by a write.
 */
interface SequenceValueSupplier
{
    public function canHandle(Storage $storage): bool;

    // Higher wins when more than one supplier reports it can handle a storage.
    public function priority(): int;

    public function next(Storage $storage, string $table, string $scope, int $year): int;
}
