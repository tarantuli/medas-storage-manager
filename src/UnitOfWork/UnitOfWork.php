<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;

class UnitOfWork
{
    /** @var \SplObjectStorage<Storage> */
    private \SplObjectStorage $storages;

    /** @var Action[] */
    private array $actions = [];

    private bool $isSorted = false;

    public function __construct()
    {
        $this->storages = new \SplObjectStorage();
    }

    public function addAction(Action $action): void
    {
        $this->storages->attach($action->storage());

        $this->actions[] = $action;
        $this->isSorted = false;
    }

    /**
     * @return \SplObjectStorage<Storage>
     */
    public function storages(): \SplObjectStorage
    {
        return $this->storages;
    }

    /** @return Action[] */
    public function actions(): array
    {
        if (!$this->isSorted) {
            $this->sortByPriority();
        }

        return $this->actions;
    }

    private function sortByPriority(): void
    {
        usort(
            $this->actions,
            fn(Action $a, Action $b) => $a->priority()->value <=> $b->priority()->value
        );

        $this->isSorted = true;
    }
}
