<?php

declare(strict_types=1);

use Medas\Cache\FilesystemCache;
use Medas\ConfigManager\ConfigManager;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Entities\Fetcher;
use Medas\StorageManager\Entities\Flusher;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;

chdir(__DIR__);

$sm = ServiceManager::get();

$cache = new FilesystemCache(__DIR__ . '/var/cache');
$cache->clear();
$sm->setCache($cache);

$sm->addPackage(StorageManagerPackage::instance());

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);
$config->readEnv(__DIR__);
$config->addDirectory(__DIR__ . '/config');

$sm->bindService($sm->resolve(Fetcher::class), \Medas\EntityManager\Entities\Fetcher::class);
$sm->bindService($sm->resolve(Flusher::class), \Medas\EntityManager\Entities\Flusher::class);

$storageManager = $sm->resolve(StorageManager::class);
$storageManager->add($sm->instantiate(Database::class));
