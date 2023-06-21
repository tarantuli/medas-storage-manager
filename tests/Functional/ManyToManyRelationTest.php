<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\ManyToMany\{Book, Label, Labels};

class ManyToManyRelationTest extends BaseTestClass
{
    public function testCreateMigration(): void
    {
        storage()->controller()->deleteStore('books__labels');
        storage()->controller()->deleteStore('books');
        storage()->controller()->deleteStore('labels');

        $migration = $this->createMigrationClassContent('ManyToMany');

        self::assertStringContainsString('class Migration', $migration);
        $this->executeMigration($migration);
    }

    /** @depends testCreateMigration */
    public function testStoring(): Book
    {
        em()->autoPersistOnCreate();

        $label1 = em()->create(Label::class, ['name' => 'label 1']);
        $label2 = em()->create(Label::class, ['name' => 'label 2']);

        $book = em()->create(Book::class, ['labels' => new Labels(fn() => [$label1, $label2])]);

        $label1Id = $label1->id();
        $bookId = $book->id();

        em()->clear();

        $book = em()->get(Book::class, $bookId);

        self::assertInstanceOf(Book::class, $book);
        self::assertInstanceOf(Labels::class, $book->labels);
        self::assertInstanceOf(Label::class, $book->labels[0]);
        self::assertEquals($label1Id, $book->labels[0]->id());

        return $book;
    }

    /** @depends testStoring */
    public function testAdding(Book $book): Book
    {
        em()->autoPersistOnCreate();

        $label3 = em()->create(Label::class, ['name' => 'label 3']);
        $book->labels[] = $label3;

        em()->flush();
        em()->clear();

        $book = em()->get(Book::class, $book->id());
        self::assertEquals(3, $book->labels->count());

        return $book;
    }

    /** @depends testAdding */
    public function testDeleting(Book $book): Book
    {
        em()->autoPersistOnCreate();

        unset($book->labels[1]);

        em()->flush();
        em()->clear();

        $book = em()->get(Book::class, $book->id());
        self::assertEquals(2, $book->labels->count());

        return $book;
    }

    /** @depends testDeleting */
    public function testAddingAndDeleting(Book $book): Book
    {
        em()->autoPersistOnCreate();

        $label4 = em()->create(Label::class, ['name' => 'label 4']);
        unset($book->labels[0]);
        $book->labels[] = $label4;

        em()->clear();

        $book = em()->get(Book::class, $book->id());
        self::assertEquals(2, $book->labels->count());

        return $book;
    }

}
