<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;

class UnitOfWork
{
    /** @var Storage[]|\SplObjectStorage */
    private array|\SplObjectStorage $storages;

    /** @var Action[] */
    private array $actions = [];

    public function __construct()
    {
        $this->storages = new \SplObjectStorage();
    }

    public function addAction(Action $action): void
    {
        $this->storages->attach($action->storage());
        $this->actions[] = $action;
        usort($this->actions, fn(Action $a, Action $b) => $a->type()->priority() <=> $b->type()->priority());
    }

    public function storages(): \SplObjectStorage
    {
        return $this->storages;
    }

    /** @return Action[] */
    public function actions(): array
    {
        return $this->actions;
    }
}
