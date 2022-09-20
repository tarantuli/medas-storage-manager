<?php

declare(strict_types=1);

namespace Medas\StorageManager\Blueprint;

class Blueprint
{
    public string $name;

    /** @var Field[] */
    public array $fields = [];

    /** @var Index[] */
    public array $indexes = [];

    /** @var ForeignKey[] */
    public array $foreignKeys = [];

    public function addField(Field $field): void
    {
        $this->fields[$field->name] = $field;
    }

    public function addIndex(Index $index): void
    {
        $this->indexes[$index->name] = $index;
    }

    public function addForeignKey(ForeignKey $foreignKey): void
    {
        // Store the foreign key with a unique name
        $name = $this->getForeignKeyName($foreignKey);
        $this->foreignKeys[$name] = $foreignKey;

        // Add an index on the field
        $index = new Index($foreignKey->field);
        $index->fields[] = $this->field($foreignKey->field);
        $this->addIndex($index);
    }

    private function getForeignKeyName(ForeignKey $foreignKey): string
    {
        $keyHash = sha1(json_encode([$this->name, $foreignKey->field, $foreignKey->foreignEntity, $foreignKey->foreignField]));

        return sprintf('mfk_%s', $keyHash);
    }

    public function field(string $name): Field|null
    {
        return isset($this->fields([$name])[0]) ? $this->fields([$name])[0] : null;
    }

    public function fields(array $names): array
    {
        return array_values(array_filter($this->fields, fn($field) => in_array($field->name, $names, true)));
    }
}
