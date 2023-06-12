<?php

declare(strict_types=1);

// 2023-06-12

namespace Sharksmedia\QueryBuilder;

class Transaction
{
    /**
     * 2023-06-12
     * @var array<int, Query|Transaction>
     */
    private array $queries = [];

    /**
     * 2023-06-12
     * @var Client
     */
    private Client $iClient;

    /**
     * 2023-06-12
     * @param Client $iClient
     */
    public static function create(Client $iClient): Transaction
    {
        $iTransaction = new static();

        $iTransaction->iClient = $iClient;

        $iClient->beginTransaction();

        return $iTransaction;
    }

    /**
     * 2023-06-12
     * @return bool
     */
    public function commit(): bool
    {// 2023-06-12
        return $this->iClient->commit();
    }

    /**
     * 2023-06-12
     * @return bool
     */
    public function rollback(): bool
    {// 2023-06-12
        return $this->iClient->rollback();
    }
}
