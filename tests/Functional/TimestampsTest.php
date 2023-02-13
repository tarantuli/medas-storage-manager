<?php

declare(strict_types=1);

namespace Medas\StorageManagerTest\Functional;

use Medas\PdoStorage\Table;
use Medas\StorageManagerTest\BaseTestClass;
use Medas\StorageManagerTest\MockUps\Attributes\TimestampedPost;

class TimestampsTest extends BaseTestClass
{
    private const TABLE_NAME = 'timestamped_posts';

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
    public function testCreateInstance(): TimestampedPost
    {
        $post = new TimestampedPost();

        em()->persist($post);
        em()->flush();

        self::assertInstanceOf(\DateTime::class, $post->createdAt());

        return $post;
    }

    /**
     * @depends testCreateInstance
     */
    public function testUpdateInstance(TimestampedPost $post): void
    {
        $post->counter++;

        em()->flush();

        self::assertNotEquals(
            $post->createdAt()->format(\DateTimeInterface::RFC3339_EXTENDED),
            $post->modifiedAt()->format(\DateTimeInterface::RFC3339_EXTENDED)
        );
    }
}
