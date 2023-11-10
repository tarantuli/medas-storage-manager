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

    //---
    case CreateRecord = 7;
    case CreateDependentRecord = 8;
    case UpdateRecord = 9;

    //---
    case UpdateCollection = 10;

    //---
    case Default = 11;

    //---
    case DeleteRecord = 12;
}
