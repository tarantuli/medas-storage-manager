<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\{RecordSet, Storage};

interface Action
{
    public function execute(): void;

    public function recordSet(): RecordSet;

    public function storage(): Storage;

    public function priority(): Priority;

    public function onComplete(): \Closure|null;

    public function setOnComplete(\Closure|null $onComplete): self;
}
