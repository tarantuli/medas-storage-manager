<?php

declare(strict_types=1);

namespace Medas\StorageManager\Migrations;

use Medas\ConfigOptions\OptionController;
use Medas\ServiceManager\Attributes\Service;
use Medas\StorageManager\ConfigOptions\MigrationsStore;
use Medas\StorageManager\Interfaces\Store;
use Medas\StorageManager\Structure\{Blueprint, Blueprint\Field, Blueprint\Index, Blueprint\Type};

#[Service]
class MigrationStoreManager
{
    private Store $store;

    public function __construct(
        private readonly OptionController $optionController,
    )
    {
    }

    public function get(): Store
    {
        if (!isset($this->store)) {
            $this->store = $this->optionController->getValue(MigrationsStore::instance());

            if (!$this->store->exists()) {
                $this->build($this->store);
            }
        }

        return $this->store;
    }

    private function build(Store $store): void
    {
        $blueprint = new Blueprint();

        $blueprint->setName($store->name());

        $migrationField = new Field('migration', Type::Text);
        $datetimeField = new Field('migrated_at', Type::DateTime);

        $blueprint->addField($migrationField);
        $blueprint->addField($datetimeField);

        $blueprint->addIndex(new Index([$migrationField]));

        foreach ($store->storage()->controller()->actionBuilder()->createStore($blueprint) as $query) {
            $query->execute();
        }
    }
}
