<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\ManyToMany\{Book, Label, Labels};

class ManyToManyRelationTest extends BaseTestClass
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('books');
        storage()->controller()->deleteStore('labels');
        storage()->controller()->deleteStore('books__labels');

        $migration = $this->createMigrationClassContent('ManyToMany');

        self::assertStringContainsString('class Migration', $migration);
        $this->executeMigration($migration);
    }

    /** @depends testCreateMigration */
    public function testStoring(): void
    {
        $label1 = em()->create(Label::class, ['name' => 'label 1']);
        $label2 = em()->create(Label::class, ['name' => 'label 2']);

        $book = em()->create(Book::class, ['labels' => new Labels(fn() => [$label1, $label2])]);
        em()->persist($label1, $label2, $book);
        em()->flush();

        $label1Id = $label1->id();
        $bookId = $book->id();

        em()->clear();

        $book = em()->get(Book::class, $bookId);

        self::assertInstanceOf(Book::class, $book);
        self::assertInstanceOf(Labels::class, $book->labels);
        self::assertInstanceOf(Label::class, $book->labels[0]);
        self::assertEquals($label1Id, $book->labels[0]->id());
    }
}
