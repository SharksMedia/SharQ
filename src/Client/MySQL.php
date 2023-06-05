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

class MySQL extends Client
{
    private Config $iConfig;
    private Database $driver;

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

		$dsn = 'mysql:host='.$iConfig->getHost().';port='.$iConfig->getPort().'dbname='.$iConfig->getDatabase().';charset='.$iConfig->getCharset();

		$pdo = new Database($dsn, $iConfig->getUser(), $iConfig->getPassword());
		
		$pdo->exec('SET sql_auto_is_null = 0');		//to fix horrible bugs: https://www.xaprb.com/blog/2007/05/31/why-is-null-doesnt-always-work-in-mysql/ & http://dev.mysql.com/doc/refman/5.6/en/server-system-variables.html#sysvar_sql_auto_is_null
		$pdo->setAttribute(Database::ATTR_CASE, Database::CASE_NATURAL);
		$pdo->setAttribute(Database::ATTR_ERRMODE, Database::ERRMODE_EXCEPTION);
		$pdo->setAttribute(Database::ATTR_ORACLE_NULLS, Database::NULL_NATURAL);
		$pdo->setAttribute(Database::ATTR_STRINGIFY_FETCHES, false);
		$pdo->setAttribute(Database::ATTR_TIMEOUT, $iConfig->getTimeout());
		
		$this->driver = $pdo;
    }

    public function wrapIdentifier(string $identifier, string $context): string
    {// 2023-05-10
        if($identifier === '*') return $identifier;

        $parts = explode('.', $identifier);
        foreach($parts as &$part)
        {
            $part = '`'.str_replace('`', '\\`', $part).'`';
        }

        return implode('.', $parts);
    }

}

