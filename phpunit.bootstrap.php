<?php

declare(strict_types=1);

use Medas\ConfigManager\ConfigManager;
use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\Databases\Pdo\Database;
use Medas\StorageManager\Entities\Fetcher;
use Medas\StorageManager\Entities\Flusher;
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StorageManagerPackage;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Contracts\Cache\CacheInterface;

chdir(__DIR__);

$sm = ServiceManager::get();
$sm->addPackage(StorageManagerPackage::instance());

/** @var ConfigManager $config */
$config = $sm->resolve(ConfigManager::class);
$config->readEnv(__DIR__);
$config->addDirectory(__DIR__ . '/config');

$cache = new ApcuAdapter('entity-manager');
$cache->clear();
$sm->bindService($cache, CacheInterface::class);

$sm->bindService($sm->resolve(Fetcher::class), \Medas\EntityManager\Entities\Fetcher::class);
$sm->bindService($sm->resolve(Flusher::class), \Medas\EntityManager\Entities\Flusher::class);

$storageManager = $sm->resolve(StorageManager::class);
$storageManager->add($sm->instantiate(Database::class));
