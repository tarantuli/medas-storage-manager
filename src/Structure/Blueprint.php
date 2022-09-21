<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

class Blueprint
{
    public string $name;

    /** @var Blueprint\Field[] */
    public array $fields = [];

    /** @var Blueprint\Index[] */
    public array $indexes = [];

    /** @var Blueprint\ForeignKey[] */
    public array $foreignKeys = [];

    public function fieldByName(string $name): Blueprint\Field|null
    {
        return isset($this->fieldsByName([$name])[0]) ? $this->fieldsByName([$name])[0] : null;
    }

    public function fieldsByName(array $names): array
    {
        return array_values(array_filter($this->fields, fn($field) => in_array($field->name, $names, true)));
    }
}
