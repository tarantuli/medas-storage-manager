<?php

declare(strict_types=1);

namespace Medas\StorageManager;

use Medas\Cache\CachePackage;
use Medas\ConfigOptions\ConfigOptionsPackage;
use Medas\Core\AsSingleton;
use Medas\EntityManager\EntityManagerPackage;
use Medas\FileBuilder\FileBuilderPackage;
use Medas\ServiceManager\{BasePackage, ServiceConfig};

class StorageManagerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            CachePackage::instance(),
            ConfigOptionsPackage::instance(),
            EntityManagerPackage::instance(),
            FileBuilderPackage::instance(),
        ];
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
