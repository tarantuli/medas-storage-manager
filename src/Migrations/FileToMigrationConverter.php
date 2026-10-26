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

        // The class the file declares; deriving it from a PSR-4 mapping can yield a name the
        // file does not declare, and autoloading that name includes the file a second time.
        // Files outside every PSR-4 directory (e.g. in a temporary directory) have no mapping.
        $className = $this->declaredClassName($filePath) ?? $this->classNameFromFile($filePath);

        if ($className === null || !class_exists($className)) {
            return null;
        }

        $class = new \ReflectionClass($className);

        if (!$class->implementsInterface(Migration::class)) {
            return null;
        }

        return new $className();
    }

    private function declaredClassName(string $filePath): string|null
    {
        $code = @file_get_contents($filePath);

        if ($code === false) {
            return null;
        }

        $namespace = '';
        $expect = null;
        $previous = null;

        foreach (\PhpToken::tokenize($code) as $token) {
            if ($token->isIgnorable()) {
                continue;
            }

            if ($expect === T_NAMESPACE) {
                if ($token->is([T_NAME_QUALIFIED, T_STRING])) {
                    $namespace = $token->text . '\\';
                }

                $expect = null;
            }
            elseif ($expect === T_CLASS) {
                return $token->is(T_STRING) ? $namespace . $token->text : null;
            }

            // Not "Foo::class" or "new class"
            elseif ($token->is(T_NAMESPACE) || ($token->is(T_CLASS) && !$previous?->is([T_DOUBLE_COLON, T_NEW]))) {
                $expect = $token->id;
            }

            $previous = $token;
        }

        return null;
    }

    /**
     * Derives the class name from the PSR-4 mapping whose directory contains the file. When
     * mappings are nested (e.g. "Tests\\" on tests/ and "Migrations\\" on tests/Migrations/),
     * the most specific directory wins; any other mapping yields a class name the file does
     * not declare, and autoloading that name includes the file a second time.
     */
    private function classNameFromFile(string $filePath): string|null
    {
        $className = null;
        $matchedLength = -1;

        foreach (spl_autoload_functions() as $loader) {
            if (!is_array($loader) || !$loader[0] instanceof ClassLoader) {
                continue;
            }

            foreach ($loader[0]->getPrefixesPsr4() as $namespace => $dirs) {
                foreach ($dirs as $dir) {
                    $realDir = realpath($dir);

                    if ($realDir === false) {
                        continue;
                    }

                    $dir = rtrim($realDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

                    if (!str_starts_with($filePath, $dir) || strlen($dir) <= $matchedLength) {
                        continue;
                    }

                    $relative = substr($filePath, strlen($dir));
                    $matchedLength = strlen($dir);

                    $className = $namespace
                        . str_replace(DIRECTORY_SEPARATOR, '\\', substr($relative, 0, -4));
                }
            }
        }

        return $className;
    }
}
