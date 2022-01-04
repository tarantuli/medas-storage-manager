<?php

declare(strict_types=1);

namespace Medas\StorageManager\Databases\Pdo\Queries;

class MysqlQueryBuilder extends BaseSqlQueryBuilder
{
    public function quote(string $identifier): string
    {
        return '`' . $identifier . '`';
    }
}
