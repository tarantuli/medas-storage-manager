<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\Interfaces\Storage;

#[Service]
class StorageManager
{
    /** @var Storage[] */
    private array $storages = [];
    private string $default;

    public function __construct(
    )
    {
    }

    public function add(Storage $storage, string $name = 'default', bool $isDefault = false)
    {
        $this->storages[$name] = $storage;

        $storage->setName($name);

        if ($isDefault || count($this->storages) === 1) {
            $this->default = $name;
        }
    }

    public function get(string $name = null): Storage
    {
        return $this->storages[$name ?? $this->default];
    }

    /** @return Storage[] */
    public function storages(): array
    {
        return $this->storages;
    }
}
