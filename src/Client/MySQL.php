<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Client;

use \Sharksmedia\QueryBuilder\Database;
use \Sharksmedia\QueryBuilder\Config;
use \Sharksmedia\QueryBuilder\Client;
use \Sharksmedia\QueryBuilder\Query;

class MySQL extends Client
{
    /** @var Config */
    private Config $iConfig;

    /** @var Database */
    private Database $driver;

    /** @var \PDOStatement[] */
    private array $preparedStatements = [];

    /** @var int */
	private $transactionCounter = 0;

    /**
     * 2023-05-08
     * @param Config $iConfig
     * @return Client
     */
    public static function create(Config $iConfig): Client
    {// 2023-05-08
        return new self($iConfig);
    }

    /**
     * 2023-05-08
     * @throws \PDOException if connection fails
     */
    protected function initializeDriver(): void
    {// 2023-05-08
        $iConfig = $this->iConfig;

		$pdo = new \PDO($this->createDSN(), $iConfig->getUser(), $iConfig->getPassword());
		
		$pdo->exec('SET sql_auto_is_null = 0');		//to fix horrible bugs: https://www.xaprb.com/blog/2007/05/31/why-is-null-doesnt-always-work-in-mysql/ & http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_sql_auto_is_null
		$pdo->setAttribute(Database::ATTR_CASE, Database::CASE_NATURAL);
		$pdo->setAttribute(Database::ATTR_ERRMODE, Database::ERRMODE_EXCEPTION);
		$pdo->setAttribute(Database::ATTR_ORACLE_NULLS, Database::NULL_NATURAL);
		$pdo->setAttribute(Database::ATTR_STRINGIFY_FETCHES, false);
		$pdo->setAttribute(Database::ATTR_TIMEOUT, $iConfig->getTimeout());
		
		$this->driver = $pdo;
    }

    /**
     * 2023-06-12
     * @param Query $iQuery
     * @return \PDOStatement
     * @throws \PDOException
     */
    public function query(Query $iQuery): \PDOStatement
    {
        $sql = $iQuery->getSQL();
        $bindings = $iQuery->getBindings();

        if(!isset($this->preparedStatements[$sql]))
        {
            $this->preparedStatements[$sql] = $this->driver->prepare($sql);
        }

        $statement = $this->preparedStatements[$sql];
        $statement->execute($bindings);

        return $statement;
    }

    public function wrapIdentifier(string $identifier, string $context): string
    {// 2023-05-10
        if($identifier === '*') return $identifier;

        $parts = explode('.', $identifier);
        foreach($parts as &$part)
        {
            if($part === '*') continue;

            $part = '`'.str_replace('`', '\\`', $part).'`';
        }

        return implode('.', $parts);
    }

    private function createDSN(): string
    {// 2023-06-12
        return sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $this->iConfig->getClient(),
            $this->iConfig->getHost(),
            $this->iConfig->getPort(),
            $this->iConfig->getDatabase(),
            $this->iConfig->getCharset()
        );
    }

    /**
     * @param string $name
     * @param array<int,mixed> $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {// 2023-06-12
        return call_user_func_array([$this->driver, $name], ...$arguments);
    }

    /**
     * @param string $name
     * @return bool
     */
	public function beginTransaction(): bool
	{// 2023-01-10
		$this->transactionCounter++;
		
		if($this->transactionCounter === 1) return $this->driver->beginTransaction();
		
		$this->driver->exec('SAVEPOINT trans'.$this->transactionCounter);
		
		return $this->transactionCounter >= 0;
	}
	
    /**
     * @param string $name
     * @return bool
     */
	public function commit(): bool
	{// 2023-01-10
		$this->transactionCounter--;
		
		if($this->transactionCounter === 0) return $this->driver->commit();
		
		return $this->transactionCounter >= 0;
	}
	
    /**
     * @param string $name
     * @return bool
     */
	public function rollback(): bool
	{// 2023-01-10
		$this->transactionCounter--;
		
		if($this->transactionCounter === 0) return $this->driver->rollback();
		
		$this->driver->exec('ROLLBACK TO trans'.($this->transactionCounter + 1));

		return true;
	}

}

