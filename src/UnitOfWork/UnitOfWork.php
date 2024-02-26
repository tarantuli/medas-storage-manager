<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Storage;

class UnitOfWork
{
    /** @var Storage[] */
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
    }

    /**
     * @return Storage[]
     * @noinspection PhpDocSignatureInspection
     */
    public function storages(): array|\SplObjectStorage
    {
        return $this->storages;
    }

    /** @return Action[] */
    public function actions(): array
    {
        $this->sortByPriority();

        return $this->actions;
    }

    private function sortByPriority(): void
    {
        usort(
            $this->actions,
            fn(Action $a, Action $b) => $a->priority()->value <=> $b->priority()->value
        );
    }
}
