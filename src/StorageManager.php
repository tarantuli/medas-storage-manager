<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Core\Attributes\Service;

#[Service]
class StorageManager
{
    /** @var Interfaces\Storage[] */
    private array $storages = [];

    private Interfaces\Storage $default;

    /** @var Interfaces\StorageController[] */
    private array $controllers;

    private array $controllerPerStorageName = [];

    public function __construct(
        ControllerRegistry $controllerRegistry,
    )
    {
        $this->controllers = $controllerRegistry->all();
    }

    /**
     * @throws Exceptions\NoControllerFoundForStorage
     * @throws Exceptions\NoDefaultStorageFound
     * @throws Exceptions\NoStorageWithNameFound
     */
    public function add(Interfaces\Storage $storage, bool $isDefault = false): void
    {
        $this->storages[$storage->name()] = $storage;

        if ($isDefault || count($this->storages) === 1) {
            $this->default = $storage;
        }

        unset($this->controllerPerStorageName[$storage->name()]);

        $this->controller($storage);
    }

    public function byName(string|null $name = null): Interfaces\Storage
    {
        if ($name === null) {
            if (!isset($this->default)) {
                throw new Exceptions\NoDefaultStorageFound();
            }

            return $this->default;
        }

        if (!array_key_exists($name, $this->storages)) {
            throw new Exceptions\NoStorageWithNameFound($name);
        }

        return $this->storages[$name];
    }

    /**
     * @throws Exceptions\NoControllerFoundForStorage
     * @throws Exceptions\NoDefaultStorageFound
     * @throws Exceptions\NoStorageWithNameFound
     */
    public function getElseSet(string $name, \Closure $storageBuilder): Interfaces\Storage
    {
        if (array_key_exists($name, $this->storages)) {
            return $this->storages[$name];
        }

        $storage = $this->storages[$name] = $storageBuilder();

        $this->controller($storage);

        return $storage;
    }

    public function controller(Interfaces\Storage|string|null $storage = null): Interfaces\StorageController
    {
        if ($storage === null) {
            if (!isset($this->default)) {
                throw new Exceptions\NoDefaultStorageFound();
            }

            $storage = $this->default;
        }
        elseif (is_string($storage)) {
            if (!array_key_exists($storage, $this->storages)) {
                throw new Exceptions\NoStorageWithNameFound($storage);
            }

            $storage = $this->storages[$storage];
        }

        $name = $storage->name();

        if (!array_key_exists($name, $this->controllerPerStorageName)) {
            $foundController = false;

            foreach ($this->controllers as $controller) {
                if (!$controller->handles($storage)) {
                    continue;
                }

                $this->controllerPerStorageName[$name] = $controller;

                $controller->initialize($storage);

                $foundController = true;

                break;
            }

            if (!$foundController) {
                throw new Exceptions\NoControllerFoundForStorage($storage);
            }
        }

        return $this->controllerPerStorageName[$name];
    }
}
