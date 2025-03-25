<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Attributes\Entity;

#[Service]
readonly class StoredEntityDeterminator
{
    public function determine(string $className, array $directories): Entity|null
    {
        $class = new \ReflectionClass($className);

        if (!$class->getFileName()) {
            return null;
        }

        $foundDirectory = false;

        foreach ($directories as $directory) {
            if (str_starts_with($class->getFileName(), $directory)) {
                $foundDirectory = true;

                break;
            }
        }

        if (!$foundDirectory) {
            return null;
        }

        if (!$entity = attribute(Entity::class, $class)) {
            return null;
        }

        if ($entity->storage === null && $entity->store === null) {
            return null;
        }

        return $entity;
    }
}
