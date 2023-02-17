<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\EntityManager\EntityManagerPackage;
use Medas\FileBuilder\FileBuilderPackage;
use Medas\ServiceManager\AsSingleton;
use Medas\ServiceManager\BasePackage;
use Medas\ServiceManager\ServiceConfig;

class StorageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return $this->dependenciesByClass([
            ConfigOptionsPackage::class,
            EntityManagerPackage::class,
            FileBuilderPackage::class,
        ]);
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }

    public function initialize(ServiceConfig $config): void
    {
        require_once __DIR__ . '/GlobalFunctions.php';
        parent::initialize($config);
    }
}
