<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManagerPackage};
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ObjectInstantiator\ObjectInstantiatorPackage;
use Medas\StorageManagerTests\StorageManagerTestsPackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig(ObjectInstantiator::class);

    $config->addPackages([
        StorageManagerPackage::instance(),
        StorageManagerTestsPackage::instance(),
        ConfigManagerPackage::instance(),
        ObjectInstantiatorPackage::instance(),
    ]);

    return $config;
});
