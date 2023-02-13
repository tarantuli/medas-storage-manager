<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Table;
use Medas\ServiceManager\Values\Interfaces\Guid;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\Attributes\GuidPost;
use Medas\StorageManagerTest\MockUps\Attributes\GuidPropertyPost;

class GuidTest extends BaseTestClass
{
    private const TABLE_NAME = 'guid_posts';

    public function testCreateTable(): void
    {
        storage()->controller()->deleteStore(self::TABLE_NAME);
        $migration = $this->createMigrationClassContent('Attributes');
        $this->executeMigration($migration);

        self::assertInstanceOf(Table::class, storage()->store(self::TABLE_NAME));
    }

    /**
     * @depends testCreateTable
     */
    public function testCreateInstance(): GuidPost
    {
        $post = new GuidPost();
        $post2 = new GuidPost();

        em()->persist($post, $post2);
        em()->flush();

        self::assertTrue($this->isGuid($post->id()));
        self::assertNotEquals($post2->id(), $post->id());

        return $post;
    }

    /**
     * @depends testCreateTable
     */
    public function testGuidProperty(): void
    {
        $post = new GuidPropertyPost();

        em()->persist($post);
        em()->flush();

        self::assertTrue($post->id() > 0);
        self::assertInstanceOf(Guid::class, $post->guid());
        self::assertTrue($this->isGuid((string) $post->guid()));
    }

    private function isGuid(string $value): bool
    {
        return (bool) preg_match('/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)})$/i', $value);
    }
}
