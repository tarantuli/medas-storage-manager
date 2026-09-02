<?php

declare(strict_types=1);

namespace Medas\StorageManager\Sequences;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Service,
    CachedImplementorList,
    Lists\SortByPriority
};
use Medas\StorageManager\{ConfigOptions\SequencesStoreName, Interfaces\Storage, StorageManager};

/**
 * Hands out gap-free, per-(scope, year) sequence numbers. Each call claims the
 * next value atomically, so numbers are unique both within a single request
 * (several calls with no flush between them still increase) and across
 * concurrent requests - unlike a max()-then-increment, which two callers can
 * read at the same value.
 *
 * Callers pick their own scope (e.g. 'order', 'subscription', 'invoice') and add
 * their own formatting (year prefix, fixed width) around the raw number.
 *
 * The backing store is only created by migrations when the
 * generate-sequences-store config option (GenerateSequencesStore) is turned on;
 * without it, there's no table for generate() to write to. Its name comes from
 * sequences-store-name (SequencesStoreName).
 */
#[Service]
readonly class SequenceGenerator
{
    private CachedImplementorList $valueSuppliers;

    public function __construct(
        private StorageManager $storageManager,

        #[ConfigValue(SequencesStoreName::class)]
        private string         $storeName,
    )
    {
        $this->valueSuppliers = new CachedImplementorList(
            SequenceValueSupplier::class,
            SortByPriority::HighToLow
        );
    }

    // The next value in the (scope, year) sequence on the given storage
    // (defaulting to the default storage), unique per call - within a request
    // (no flush needed between calls) and across concurrent requests.
    public function generate(string $scope, int $year, Storage|null $storage = null): int
    {
        $storage ??= $this->storageManager->byName();

        foreach ($this->valueSuppliers->get() as $supplier) {
            /** @var SequenceValueSupplier $supplier */
            if ($supplier->canHandle($storage)) {
                return $supplier->next($storage, $this->storeName, $scope, $year);
            }
        }

        throw new NoSequenceValueSupplier($storage);
    }
}
