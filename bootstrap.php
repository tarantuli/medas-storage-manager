<?php

use Medas\ServiceManager\ServiceManager;
use Medas\StorageManager\StorageManagerPackage;

require_once 'vendor/autoload.php';

$sm = ServiceManager::get();

$sm->addPackage(StorageManagerPackage::instance());
