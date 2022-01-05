<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;
use Medas\StorageManager\UnitOfWork\ActionTypes\ActionType;

abstract class BaseAction implements Action
{
    protected Storage $storage;
    protected ActionType $type;
    private \Closure|null $onComplete = null;

    public function storage(): Storage
    {
        return $this->storage;
    }

    public function onComplete(): \Closure|null
    {
        return $this->onComplete;
    }

    public function setOnComplete(\Closure|null $onComplete): self
    {
        $this->onComplete = $onComplete;

        return $this;
    }

    public function type(): ActionType
    {
        return $this->type;
    }

    public function setType(ActionType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
