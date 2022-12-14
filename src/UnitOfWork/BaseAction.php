<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;

abstract class BaseAction implements Action
{
    protected Storage $storage;
    protected Priority $priority = Priority::Default;
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

    public function priority(): Priority
    {
        return $this->priority;
    }

    public function setPriority(Priority $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
