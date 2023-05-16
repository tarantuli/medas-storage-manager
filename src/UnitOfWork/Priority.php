<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

enum Priority: int
{
    case CreateStore = 1;
    case DeleteStoreRelations = 2;
    case AlterStore = 3;
    case AddCollectionStore = 4;
    case AddStoreRelations = 5;
    case DeleteStore = 6;

    case CreateRecord = 7;
    case UpdateRecord = 8;

    case UpdateCollection = 9;

    case Default = 10;

    case DeleteRecord = 11;
}
