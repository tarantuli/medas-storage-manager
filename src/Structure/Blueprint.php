<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

class Blueprint
{
    private string $name;

    /** @var Blueprint\Field[] */
    private array $fields = [];

    /** @var Blueprint\Index[] */
    private array $indexes = [];

    /** @var Blueprint\ForeignKey[] */
    private array $foreignKeys = [];

    public function name(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function addField(Blueprint\Field $field): void
    {
        $this->fields[] = $field;
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function fieldByName(string $name): Blueprint\Field|null
    {
        return $this->fieldsByName([$name])[0] ?? null;
    }

    public function fieldsByName(array $names): array
    {
        return array_values(array_filter($this->fields, fn($field) => in_array($field->name, $names, true)));
    }

    public function addIndex(Blueprint\Index $index): void
    {
        $this->indexes[] = $index;
    }

    public function indexes(): array
    {
        return $this->indexes;
    }

    public function addForeignKey(Blueprint\ForeignKey $foreignKey): void
    {
        $this->foreignKeys[] = $foreignKey;
    }

    public function foreignKeys(): array
    {
        return $this->foreignKeys;
    }
}
