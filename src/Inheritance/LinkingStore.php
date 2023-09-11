<?php

declare(strict_types=1);

namespace Medas\StorageManager\Inheritance;

use Medas\Core\Attributes\Service;
use Medas\StorageManager\Interfaces\{ActionExecutor, Storage, StorageController};
use Medas\StorageManager\Structure\Blueprint;
use Medas\StorageManager\UnitOfWork\ActionSet;

#[Service]
readonly class LinkingStore implements OriginalClassStorageStrategy
{
    public function __construct(
        private StorageController $storageController,
        private ActionExecutor    $actionExecutor,
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
            name: 'entity_class',
            type: Blueprint\Type::Text,
            isGenerated: false,
        );

        $primaryIndex = new Blueprint\Index(
            [$idField],
            true
        );

        $linkStoreBlueprint->name = $this->determineLinkStoreName($blueprint->name);
        $linkStoreBlueprint->storeOriginalClass = false;

        $linkStoreBlueprint
            ->addField($idField)
            ->addField($valueField)
            ->addIndex($primaryIndex)
            ->addForeignKey($idForeignKey);

        return $this->storageController->migrationBuilder()->buildActions($storage, $linkStoreBlueprint);
    }

    public function createValuesToStore(Blueprint $blueprint, object $entity): array
    {
        $storeName = $this->determineLinkStoreName($blueprint->storeRequestingOriginalClassStorage);

        return [$storeName => ['entity_class' => $entity::class]];
    }

    private function determineLinkStoreName(string $sourceTable): string
    {
        return $sourceTable . '__original_class';
    }

    public function getOriginalClass(Blueprint $blueprint, mixed $id): string
    {
        $actions = $this->storageController->actionBuilders()->get()->build(
            [$this->storageController->store($this->determineLinkStoreName($blueprint->storeRequestingOriginalClassStorage))],
            [$blueprint->idField()->name => $id]
        );

        $this->actionExecutor->executeSet($actions);

        return $actions->lastRecordSet->fetchRecord()['entity_class'];
    }
}
