<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Table;
use Medas\StorageManagerTest\BaseTest;
use Medas\StorageManagerTest\MockUps\Attributes\GuidPost;

class GuidTest extends BaseTest
{
    private const TABLE_NAME = 'guid_posts';

    public function testCreateTable(): void
    {
        storage()->controller()->deleteStore(self::TABLE_NAME);
        $migration = $this->createMigrationClassContent();
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

    private function isGuid(string $value): bool
    {
        return (bool) preg_match('/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)})$/i', $value);
    }
}
