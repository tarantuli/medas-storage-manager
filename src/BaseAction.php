<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\StorageManager\Interfaces\Action;
use Medas\StorageManager\Interfaces\Storage;

abstract class BaseAction implements Action
{
    protected Storage $storage;
    private \Closure|null $onComplete = null;

    public function storage(): Storage
    {
        return $this->storage;
    }

    public function setStorage(Storage $database): self
    {
        $this->storage = $database;

        return $this;
    }

    public function onComplete(): ?\Closure
    {
        return $this->onComplete;
    }

    public function setOnComplete(\Closure|null $onComplete): self
    {
        $this->onComplete = $onComplete;

        return $this;
    }
}
