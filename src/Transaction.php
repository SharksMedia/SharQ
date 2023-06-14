<?php

declare(strict_types=1);

// 2023-06-12

namespace Sharksmedia\QueryBuilder;

class Transaction
{
    public const ISOLATION_READ_UNCOMMITTED     = 'READ UNCOMMITTED';
    public const ISOLATION_READ_COMMITTED       = 'READ COMMITTED';
    public const ISOLATION_REPEATABLE_READ      = 'REPEATABLE READ';
    public const ISOLATION_SERIALIZABLE         = 'SERIALIZABLE';

    private static $transactionCounter = 0;

    private string $transactionID;

    /**
     * 2023-06-12
     * @var array<int, Query|Transaction>
     */
    private array $queries = [];

    /**
     * 2023-06-14
     * @var string
     */
    private string $isolationLevel;

    /**
     * 2023-06-12
     * @var Client
     */
    private Client $iClient;

    /**
     * 2023-06-12
     * @param Client $iClient
     * @return Transaction
     */
    public static function create(Client $iClient): Transaction
    {
        $iTransaction = new static();

        $iTransaction->iClient = $iClient;

        $iTransaction->initialize();

        // $iClient->beginTransaction();

        return $iTransaction;
    }

    // /**
    //  * 2023-06-12
    //  * @return bool
    //  */
    // public function commit(): bool
    // {// 2023-06-12
    //     return $this->iClient->commit();
    // }
    //
    // /**
    //  * 2023-06-12
    //  * @return bool
    //  */
    // public function rollback(): bool
    // {// 2023-06-12
    //     return $this->iClient->rollback();
    // }

    /**
     * 2023-06-14
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->iClient;
    }

    /**
     * 2023-06-14
     */
    public function initialize()
    {// 2023-06-14
        self::$transactionCounter++;

        $this->transactionID = 'transaction_'.self::$transactionCounter;

        if($this->iClient->isTransacting())
        {
            $this->savepoint();
        }
        else
        {
            $this->begin();
        }
    }

    /**
     * 2023-06-14
     * @return string
     */
    public function getID(): string
    {// 2023-06-14
        return $this->transactionID;
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function begin()
    {
        // FIXME: Set isolation level

        return $this->iClient->beginTransaction();
        // return $this->query('BEGIN;');
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function savepoint()
    {
        return $this->query("SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function commit()
    {
        if($this->iClient->isTransacting()) return $this->release();

        return $this->iClient->commit();
        // return $this->query('COMMIT;');
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function release()
    {
        return $this->query("RELEASE SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     */
    public function setIsolationLevel(string $isolationLevel)
    {
        $this->isolationLevel = $isolationLevel;
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function rollback()
    {
        if($this->iClient->isTransacting()) return $this->rollbackTo();

        return $this->iClient->rollback();
        // return $this->query('ROLLBACK;');
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function rollbackTo()
    {
        return $this->query("ROLLBACK TO SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function query(string $query)
    {
        $iQuery = new Query('SELECT', [], 5000, false, [],  '');

        $iQuery->setSQL($query);

        return $this->iClient->query($iQuery);
    }
}
