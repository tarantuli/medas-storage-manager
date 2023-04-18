<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Interfaces\Storage;

#[Service]
class StorageManager
{
    /** @var Storage[] */
    private array $storages = [];
    private string $default;

    public function __construct()
    {
    }

    public function add(Storage $storage, string $name = 'default', bool $isDefault = false): void
    {
        $this->storages[$name] = $storage;

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

    public function getName(Storage $storage): string
    {
        $name = array_search($storage, $this->storages, true);

        if ($name === false) {
            throw new \Exception('unknown storage');
        }

        return $name;
    }
}
