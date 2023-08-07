<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\EntityManager\Entities\{Fetcher, Flusher};
use Medas\FileBuilder\FileBuilderPackage;
use Medas\PdoMysql\PdoMysqlPackage;
use Medas\PdoStorage\Database;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\Entities\{ChangeFlusher, RecordManager};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        StorageManagerPackage::instance(),
        ConfigManagerPackage::instance(),
        ConsolePrinterPackage::instance(),
        PdoMysqlPackage::instance(),
        RamseyUuidBridgePackage::instance(),
        FileBuilderPackage::instance(),
    ]);

    return $config;
});

service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory(__DIR__ . '/config');

sm()->bindImplementation(service(RecordManager::class), Fetcher::class);
sm()->bindImplementation(service(ChangeFlusher::class), Flusher::class);

service(StorageManager::class)
    ->add(medas()->objectInstantiator()->instantiate(Database::class));
