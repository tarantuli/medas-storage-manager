<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\MockUps\Relations;

use Medas\Core\Collections\LazyGenericCollection;
use Medas\EntityManager\Attributes\EntityCollection;

/** @extends LazyGenericCollection<Label> */
#[EntityCollection(Label::class)]
class Labels extends LazyGenericCollection
{
}
