<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\ManyToMany\Book;
use Medas\StorageManagerTest\MockUps\ManyToMany\Label;

class ManyToManyRelationTest extends BaseTestClass
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('books');
        storage()->controller()->deleteStore('labels');

        $migration = $this->createMigrationClassContent('ManyToMany');

        self::assertStringContainsString('class Migration', $migration);
        $this->executeMigration($migration);
    }

    /** @depends testCreateMigration */
    public function testStoring(): void
    {
        $label1 = em()->create(Label::class, ['name' => 'label 1']);
        $label2 = em()->create(Label::class, ['name' => 'label 2']);

        $book1  =em()->create(Book::class, ['labels' => [$label1, $label2]]);
        em()->persist($label1, $label2, $book1);
        em()->flush();
    }
}
