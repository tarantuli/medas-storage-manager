<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ConfigManager\ConfigManagerPackage;
use Medas\ConsolePrinter\ConsolePrinterPackage;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Entities\Fetcher;
use Medas\StorageManager\Entities\Flusher;
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

$sm = ServiceManager::get();

$sm->addPackage(StorageManagerPackage::instance())
    ->addPackage(ConfigManagerPackage::instance())
    ->addPackage(ConsolePrinterPackage::instance());

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);
$config->readEnv(__DIR__);
$config->addDirectory(__DIR__ . '/config');

$sm->bindService($sm->resolve(Fetcher::class), \Medas\EntityManager\Entities\Fetcher::class);
$sm->bindService($sm->resolve(Flusher::class), \Medas\EntityManager\Entities\Flusher::class);
