<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

enum Priority: int
{
    case CreateStore = 1;
    case DeleteStoreRelations = 2;
    case AlterStore = 3;
    case AddStoreRelations = 4;
    case DeleteStore = 5;

    case CreateRecord = 6;
    case UpdateRecord = 7;

    case Default = 8;

    case DeleteRecord = 9;
}
