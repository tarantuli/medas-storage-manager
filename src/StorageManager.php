<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Interfaces\{Storage, StorageController};

#[Service]
class StorageManager
{
    /** @var Storage[] */
    private array $storages = [];

    private Storage $default;

    /** @var StorageController[] */
    private array $controllers = [];

    private array $controllerPerStorageName = [];

    public function __construct()
    {
    }

    public function add(Storage $storage, bool $isDefault = false): void
    {
        $this->storages[$storage->name()] = $storage;

        if ($isDefault || count($this->storages) === 1) {
            $this->default = $storage;
        }

        unset($this->controllerPerStorageName[$storage->name()]);
        $this->controller($storage);
    }

    public function byName(string $name = null): Storage
    {
        return $name === null ? $this->default : $this->storages[$name];
    }

    public function controller(Storage|string $storage = null): StorageController
    {
        if ($storage === null) {
            $storage = $this->default;
        }
        elseif (is_string($storage)) {
            $storage = $this->storages[$storage];
        }

        $name = $storage->name();

        if (!array_key_exists($name, $this->controllerPerStorageName)) {
            $foundController = false;
            foreach ($this->controllers as $controller) {
                if ($controller->handles($storage)) {
                    $this->controllerPerStorageName[$name] = $controller;
                    $foundController = true;
                    break;
                }
            }

            if (!$foundController) {
                throw new \Exception('no controller found for ' . $name);
            }
        }

        return $this->controllerPerStorageName[$name];
    }

    public function registerController(StorageController $controller): void
    {
        $this->controllers[] = $controller;
    }
}
