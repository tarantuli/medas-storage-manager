<?php

declare(strict_types=1);

namespace Medas\StorageManager\UnitOfWork;

use Medas\StorageManager\Interfaces\Action;
use Medas\StorageManager\Interfaces\Storage;

class UnitOfWork
{
    /** @var Storage[]|\SplObjectStorage */
    public array|\SplObjectStorage $storages;

    /** @var Action[] */
    public array $creates = [];

    /** @var Action[] */
    public array $updates = [];

    /** @var \Medas\StorageManager\Interfaces\Action[] */
    public array $additionalActions = [];

    public function __construct()
    {
        $this->storages = new \SplObjectStorage();
    }

    public function addUpdate(Action $update): void
    {
        $this->storages->attach($update->storage());
        $this->updates[] = $update;
    }

    public function addCreate(Action $create): void
    {
        $this->storages->attach($create->storage());
        $this->creates[] = $create;
    }

    public function addAction(Action $create): void
    {
        $this->storages->attach($create->storage());
        $this->additionalActions[] = $create;
    }
}
