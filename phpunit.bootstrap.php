<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\EntityManager\Entities\{EntityValueFetcher, Flusher, SelectorRecordsFetcher};
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\Entities\{ChangeFlusher, EntityValueManager, StoreRecordManager};
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        StorageManagerPackage::instance(),
        ConfigManagerPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory(__DIR__ . '/config');

sm()->bindImplementation(service(StoreRecordManager::class), SelectorRecordsFetcher::class);
sm()->bindImplementation(service(EntityValueManager::class), EntityValueFetcher::class);
sm()->bindImplementation(service(ChangeFlusher::class), Flusher::class);
