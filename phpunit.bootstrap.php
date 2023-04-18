<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\Core\GlobalRepository;
use Medas\FileBuilder\FileBuilderPackage;
use Medas\PdoStorage\{Database, PdoStoragePackage};
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\{ServiceConfig, ServiceManager};
use Medas\StorageManager\Entities\{Fetcher, Flusher};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

new ServiceManager(function (): ServiceConfig {
    $config = new ServiceConfig();

    $config->addPackages([
        StorageManagerPackage::instance(),
        ConfigManagerPackage::instance(),
        ConsolePrinterPackage::instance(),
        PdoStoragePackage::instance(),
        RamseyUuidBridgePackage::instance(),
        FileBuilderPackage::instance(),
    ]);

    return $config;
});

/** @var ConfigManager $config */
service(ConfigManager::class)
    ->readEnv(__DIR__)
    ->addDirectory(__DIR__ . '/config');

sm()->bindImplementation(service(Fetcher::class), \Medas\EntityManager\Entities\Fetcher::class);
sm()->bindImplementation(service(Flusher::class), \Medas\EntityManager\Entities\Flusher::class);

service(StorageManager::class)
    ->add(GlobalRepository::objectInstantiator()->instantiate(Database::class));
