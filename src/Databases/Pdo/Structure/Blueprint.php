<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Structure;

class Blueprint
{
    public string $name;

    /** @var Blueprint\Field[] */
    public array $fields = [];

    /** @var Blueprint\Index[] */
    public array $indexes = [];

    public function addField(Blueprint\Field $field): void
    {
        $this->fields[$field->name] = $field;
    }

    public function addIndex(Blueprint\Index $index): void
    {
        $this->indexes[$index->name] = $index;
    }

    public function field(string $name): Blueprint\Field
    {
        return $this->fields([$name])[0];
    }

    public function fields(array $names): array
    {
        return array_values(array_filter($this->fields, fn($field) => in_array($field->name, $names, true)));
    }
}
