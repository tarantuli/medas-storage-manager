<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\UnitOfWork\ActionTypes\ActionType;

interface Action
{
    public function execute(): void;

    public function storage(): Storage;

    public function onComplete(): \Closure|null;

    public function setOnComplete(\Closure|null $onComplete): self;

    public function type(): ActionType;

    public function setType(ActionType $type): self;
}
