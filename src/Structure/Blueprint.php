<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure;

class Blueprint
{
    private string|null $name;

    private string|null $parent;

    /** @var Blueprint\Field[] */
    private array $fields = [];

    /** @var Blueprint\Index[] */
    private array $indexes = [];

    /** @var Blueprint\ForeignKey[] */
    private array $foreignKeys = [];

    public function name(): string|null
    {
        return $this->name;
    }

    public function setName(string|null $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addField(Blueprint\Field $field): self
    {
        $this->fields[] = $field;

        return $this;
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

    public function addIndex(Blueprint\Index $index): self
    {
        $this->indexes[] = $index;

        return $this;
    }

    public function indexes(): array
    {
        return $this->indexes;
    }

    public function indexByHash(string $hash): Blueprint\Index|null
    {
        return $this->indexesByHash([$hash])[0] ?? null;
    }

    public function primaryIndex(): Blueprint\Index|null
    {
        foreach ($this->indexes as $index) {
            if ($index->isPrimary) {
                return $index;
            }
        }

        return null;
    }

    public function indexesByHash(array $hashes): array
    {
        return array_values(array_filter($this->indexes, fn($index) => in_array($index->hash(), $hashes, true)));
    }

    public function addForeignKey(Blueprint\ForeignKey $foreignKey): self
    {
        $this->foreignKeys[] = $foreignKey;

        return $this;
    }

    public function foreignKeys(): array
    {
        return $this->foreignKeys;
    }

    public function foreignKeyByHash(string $hash): Blueprint\ForeignKey|null
    {
        return $this->foreignKeysByHash([$hash])[0] ?? null;
    }

    public function foreignKeysByHash(array $hashes): array
    {
        return array_values(array_filter($this->foreignKeys, fn($foreignKey) => in_array($foreignKey->hash(), $hashes, true)));
    }

    public function parent(): string|null
    {
        return $this->parent;
    }

    public function setParent(string|null $parent): self
    {
        $this->parent = $parent;

        return $this;
    }
}
