<?php

declare(strict_types=1);

namespace Medas\StorageManager\Structure\Changes;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Structure\Blueprint;

#[Service]
class ChangeFinder
{
    private Blueprint $expected;
    private Blueprint $existing;

    private bool $foundChanges;
    private Changes $changes;

    public function find(Blueprint $expected, Blueprint $existing): Changes|null
    {
        $this->expected = $expected;
        $this->existing = $existing;
        $this->foundChanges = false;
        $this->changes = new Changes($expected->name());

        $this->checkFields();
        $this->checkIndexes();
        $this->checkForeignKeys();

        return $this->foundChanges ? $this->changes : null;
    }

    private function checkFields(): void
    {
        foreach ($this->expected->fields() as $field) {
            if ($current = $this->existing->fieldByName($field->name)) {
                if ($this->areFieldsComparable($field, $current)) {
                    continue;
                }

                $this->changes->changeFields[] = $field;
            }
            else {
                $this->changes->addFields[] = $field;
            }

            $this->foundChanges = true;
        }
    }

    private function checkIndexes(): void
    {
        foreach ($this->expected->indexes() as $index) {
            if (!$this->existing->indexByHash($index->hash())) {
                $this->changes->indexes[] = $index;
            }

            $this->foundChanges = true;
        }
    }

    private function checkForeignKeys(): void
    {
        foreach ($this->expected->foreignKeys() as $foreignKey) {
            if ($current = $this->existing->foreignKeyByHash($foreignKey->hash())) {
                if ($this->areForeignKeysComparable($foreignKey, $current)) {
                    continue;
                }

                $this->changes->changeForeignKey[] = $foreignKey;
            }
            else {
                $this->changes->addForeignKey[] = $foreignKey;
            }

            $this->foundChanges = true;
        }
    }

    private function areFieldsComparable(Blueprint\Field $field, Blueprint\Field $current): bool
    {
        $diff = array_udiff_assoc((array) $field, (array) $current, fn($a, $b) => $a <=> $b);

        // If the diff contains length or value parameters, compare ranges
        if (isset($diff['minLength']) and $current->minLength <= $field->minLength) {
            unset($diff['minLength']);
        }

        if (isset($diff['maxLength']) and $current->maxLength >= $field->maxLength) {
            unset($diff['maxLength']);
        }

        if (isset($diff['minValue']) and $current->minValue <= $field->minValue) {
            unset($diff['minValue']);
        }

        if (isset($diff['maxValue']) and $current->maxValue >= $field->maxValue) {
            unset($diff['maxValue']);
        }

        foreach ($diff as $key => $value) {
            $fieldValue = $field->$key;
            $currentValue = $current->$key;

            if ($fieldValue === Blueprint\Type::Boolean && $currentValue === Blueprint\Type::Integer) {
                unset($diff[$key]);
                continue;
            }

            if (is_object($value) && enum_exists($value::class)) {
                // Compare by backed value
                if ($fieldValue->value == $currentValue) {
                    unset($diff[$key]);
                    continue;
                }
            }

            if ($current->hasDefault === false
                && $field->hasDefault === true
                && $field->default === null) {
                unset($diff[$key]);
                /** @noinspection PhpUnnecessaryStopStatementInspection */
                continue;
            }
        }

        return $diff === [];
    }

    private function areForeignKeysComparable(Blueprint\ForeignKey $foreignKey, Blueprint\ForeignKey $current): bool
    {
        return $foreignKey->doCascade === $current->doCascade;
    }
}
