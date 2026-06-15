<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManagerPackage};
use Medas\ObjectInstantiator\ObjectInstantiator;
use Medas\ObjectInstantiator\ObjectInstantiatorPackage;
use Medas\StorageManagerTests\StorageManagerTestsPackage;
use Medas\ServiceManager\{ServiceConfigBuilder, ServiceManager};
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfigBuilder {
    $config = new ServiceConfigBuilder(ObjectInstantiator::class);

    $config->addPackages([
        StorageManagerPackage::instance(),
        StorageManagerTestsPackage::instance(),
        ConfigManagerPackage::instance(),
        ObjectInstantiatorPackage::instance(),
    ]);

    return $config;
});
