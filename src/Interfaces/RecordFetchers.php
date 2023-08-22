<?php

declare(strict_types=1);

namespace Medas\StorageManager\Interfaces;

/**
 * This should be a service that returns services
 */
interface RecordFetchers
{
    public function filteredFetcher(): Fetchers\FilteredFetcher;

    public function collectionRecordFetcher(): Fetchers\CollectionRecordFetcher;
}
