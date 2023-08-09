<?php

declare(strict_types=1);

// 2023-06-12

namespace Sharksmedia\QueryBuilder;

use function PHPUnit\Framework\throwException;

class Transaction
{
    public const ISOLATION_READ_UNCOMMITTED     = 'READ UNCOMMITTED';
    public const ISOLATION_READ_COMMITTED       = 'READ COMMITTED';
    public const ISOLATION_REPEATABLE_READ      = 'REPEATABLE READ';
    public const ISOLATION_SERIALIZABLE         = 'SERIALIZABLE';

    public const INTENT_SHARED_LOCK             = 'LOCK MODE IS';
    public const INTENT_UPDATE_LOCK             = 'LOCK MODE IX';

    private static $transactionCounter = 0;

    private string $transactionID;

    /**
     * 2023-06-12
     * @var array<int, QueryBuilder>
     */
    private array $queries = [];

    /**
     * 2023-06-14
     * @var string
     */
    private ?string $isolationLevel = self::ISOLATION_REPEATABLE_READ;

	private string $currentIsolationLevel = self::ISOLATION_REPEATABLE_READ;
	private string $defaultIsolationLevel = self::ISOLATION_REPEATABLE_READ;

    /**
     * 2023-06-12
     * @var Client
     */
    private Client $iClient;

    private bool $started = false;

    public function __construct()
    {
        self::$transactionCounter++;
    }

    /**
     * 2023-06-12
     * @param Client $iClient
     * @return Transaction
     */
    public static function create(): Transaction
    {
        $iTransaction = new static();

        return $iTransaction;
    }

    public function start(Client $iClient)
    {
        if(!$this->isStarted()) $this->initialize($iClient);

        $this->started = true;
    }

    public function isStarted(): bool
    {
        return $this->started;
    }

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
    protected function initialize(Client $iClient): void
    {// 2023-06-14
        $this->iClient = $iClient;

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
    protected function begin()
    {
        if($this->isolationLevel !== null)
        {
            $this->query("SET TRANSACTION ISOLATION LEVEL {$this->isolationLevel};");

            $this->currentIsolationLevel = $this->isolationLevel;
        }

        return $this->iClient->beginTransaction();
    }

    /**
     * 2023-06-14
     * @return bool
     */
    protected function savepoint()
    {
        if(!$this->isStarted()) throw new \Exception('Transaction not started');

        return $this->query("SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function commit()
    {
        if($this->iClient->isTransacting()) return $this->release();

        $this->iClient->commit();

        if($this->currentIsolationLevel !== $this->defaultIsolationLevel)
        {
            $this->query("SET TRANSACTION ISOLATION LEVEL {$this->defaultIsolationLevel};");

            $this->currentIsolationLevel = $this->defaultIsolationLevel;
        }

        $this->started = false;
    }

    /**
     * 2023-06-14
     * @return bool
     */
    protected function release()
    {
        if(!$this->isStarted()) throw new \Exception('Transaction not started');

        return $this->query("RELEASE SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     */
    public function setIsolationLevel(string $isolationLevel)
    {
        if($this->isStarted()) throw new \Exception('Transaction already started');

        $this->isolationLevel = $isolationLevel;
    }

    /**
     * 2023-06-14
     * @return bool
     */
    public function rollback()
    {
        if(!$this->isStarted()) throw new \Exception('Transaction not started');

        if($this->iClient->isTransacting()) return $this->rollbackTo();

        return $this->iClient->rollback();
        // return $this->query('ROLLBACK;');
    }

    /**
     * 2023-06-14
     * @return bool
     */
    protected function rollbackTo()
    {
        if(!$this->isStarted()) throw new \Exception('Transaction not started');

        return $this->query("ROLLBACK TO SAVEPOINT {$this->getID()};");
    }

    /**
     * 2023-06-14
     * @return bool
     */
    protected function query(string $query)
    {
        $iQuery = new Query('SELECT', [], 5000, false, [],  '');

        $iQuery->setSQL($query);

        return $this->iClient->query($iQuery);
    }
}
