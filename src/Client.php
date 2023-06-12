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
    private \PDO $driver;
    private Config $iConfig;

    public function __construct(Config $iConfig)
    {// 2023-05-08
        $this->iConfig = $iConfig;
    }

    abstract public function query(Query $iQuery, array $options=[]): \PDOStatement; // Execute query

    abstract public static function create(Config $iConfig): self; // Create new instance of self
    abstract protected function initializeDriver(): void; // Create new PDO
    abstract public function wrapIdentifier(string $identifier, string $context): string; // Wrap identifier in quotes

    abstract public function beginTransaction(): bool;
    abstract public function commit(): bool;
    abstract public function rollback(): bool;
    
    public function getQueryCompiler(QueryBuilder $iQueryBuilder): QueryCompiler
    {// 2023-05-10
        return new QueryCompiler($this, $iQueryBuilder, []);
    }
}
