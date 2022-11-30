<?php

declare(strict_types=1);

use Medas\ConfigManager\{ConfigManager, ConfigManagerPackage};
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\PdoStorage\Database;
use Medas\PdoStorage\PdoStoragePackage;
use Medas\RamseyUuidBridge\RamseyUuidBridgePackage;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Entities\{Fetcher, Flusher};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

$sm = ServiceManager::get();

$sm->addPackage(StorageManagerPackage::instance())
    ->addPackage(ConfigManagerPackage::instance())
    ->addPackage(ConsolePrinterPackage::instance())
    ->addPackage(PdoStoragePackage::instance())
    ->addPackage(RamseyUuidBridgePackage::instance());

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);
$config->readEnv(__DIR__);
$config->addDirectory(__DIR__ . '/config');

$sm->bindService($sm->resolve(Fetcher::class), \Medas\EntityManager\Entities\Fetcher::class);
$sm->bindService($sm->resolve(Flusher::class), \Medas\EntityManager\Entities\Flusher::class);

service(StorageManager::class)->add(
    sm()->instantiate(Database::class)
);
