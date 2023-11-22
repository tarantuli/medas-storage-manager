<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\StorageManager\ConfigOptions\OriginalClassStorage\LinkingStore\StoreNamingStrategy;
use Medas\StorageManager\Interfaces\{ActionExecutor, Storage, StorageController};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class LinkingStore implements OriginalClassStorageStrategy
{
    public function __construct(
        private ActionExecutor             $actionExecutor,
        private StorageController          $storageController,

        #[ConfigValue(StoreNamingStrategy::class)]
        private LinkinStore\NamingStrategy $namingStrategy,
    )
    {
    }

    public function buildStoreActions(Blueprint $blueprint, Storage $storage): ActionSet
    {
        $linkStoreBlueprint = new Blueprint();
        $idField = clone $blueprint->primaryIndex()->fields()[0];
        $idField->name = 'id';
        $idField->isGenerated = false;

        $idForeignKey = new Blueprint\ForeignKey(
            'id',
            $blueprint->name,
            $blueprint->primaryIndex()->fields()[0]->name,
            true
        );

        $valueField = new Blueprint\Field(
            name: 'entityClass',
            type: Blueprint\Type::Text,
            isGenerated: false,
        );

        $primaryIndex = new Blueprint\Index([$idField], true);
        $linkStoreBlueprint->name = $this->namingStrategy->determine($blueprint->name);

        $linkStoreBlueprint
            ->addField($idField)
            ->addField($valueField)
            ->addIndex($primaryIndex)
            ->addForeignKey($idForeignKey);

        return $this->storageController->migrationBuilder()->buildActions(
            $storage,
            $linkStoreBlueprint
        );
    }

    public function createValuesToStore(Blueprint $blueprint, object $entity): array
    {
        $storeName = $this->namingStrategy->determine($blueprint->storeRequestingOriginalClassStorage);

        return [$storeName => ['entityClass' => $entity::class]];
    }

    public function getOriginalClass(Blueprint $blueprint, mixed $id): string
    {
        $actions = $this->storageController->actionBuilders()->get()->build(
            [$this->storageController->store($this->namingStrategy->determine($blueprint->storeRequestingOriginalClassStorage))],
            [$blueprint->idField()->name => $id]
        );

        $this->actionExecutor->executeSet($actions);

        return $actions->lastRecordSet->fetchRecord()['entityClass'];
    }
}
