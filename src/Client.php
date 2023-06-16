<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

use Sharksmedia\QueryBuilder\Config;

abstract class Client
{
    public const TYPE_MYSQL = 'mysql';

    protected  Config $iConfig;

    public function __construct(Config $iConfig)
    {// 2023-05-08
        $this->iConfig = $iConfig;
    }

    abstract public function query(Query $iQuery, array $options=[]): \PDOStatement; // Execute query

    public static function create(Config $iConfig): Client
    {// 2023-06-14
        switch ($iConfig->getClient()) {
            case self::TYPE_MYSQL:
                $iClient = new Client\MySQL($iConfig);
                // $iClient->initializeDriver();
                return $iClient;
            default:
                throw new \Exception('Unknown client type: ' . $iConfig->getClient());
        }
    }

    public function getConfig(): Config
    {// 2023-05-08
        return $this->iConfig;
    }

    abstract public function initializeDriver(): void; // Create new PDO
    abstract public function wrapIdentifier(string $identifier, string $context): string; // Wrap identifier in quotes

    abstract public function isTransacting(): bool;
    abstract public function beginTransaction(): bool;
    abstract public function commit(): bool;
    abstract public function rollback(): bool;
    
    public function getQueryCompiler(QueryBuilder $iQueryBuilder): QueryCompiler
    {// 2023-05-10
        return new QueryCompiler($this, $iQueryBuilder, []);
    }
}
