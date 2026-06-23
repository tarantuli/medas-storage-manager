<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Composer\Autoload\ClassLoader;
use Medas\Core\Attributes\Service;

#[Service]
readonly class FileToMigrationConverter
{
    public function convert(string $filePath): Migration|null
    {
        if (!str_ends_with($filePath, '.php')) {
            return null;
        }

        $className = $this->classNameFromFile($filePath);

        if ($className === null || !class_exists($className)) {
            return null;
        }

        $class = new \ReflectionClass($className);

        if (!$class->implementsInterface(Migration::class)) {
            return null;
        }

        return new $className();
    }

    private function classNameFromFile(string $filePath): string|null
    {
        foreach (spl_autoload_functions() as $loader) {
            if (!is_array($loader) || !$loader[0] instanceof ClassLoader) {
                continue;
            }

            foreach ($loader[0]->getPrefixesPsr4() as $namespace => $dirs) {
                foreach ($dirs as $dir) {
                    $dir = rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                    if (!str_starts_with($filePath, $dir)) {
                        continue;
                    }

                    $relative = substr($filePath, strlen($dir));

                    return $namespace
                        . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));
                }
            }
        }

        return null;
    }
}
