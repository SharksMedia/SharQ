<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

use PDO;
use Sharksmedia\QueryBuilder\Config;
use Sharksmedia\QueryBuilder\Statement\Raw;

abstract class Client
{
    private \PDO $driver;
    private Config $iConfig;

    public function __construct(Config $iConfig)
    {// 2023-05-08
        
    }

    abstract public static function create(Config $iConfig): self; // Create new instance of self
    abstract protected function initializeDriver(): void; // Create new PDO
    abstract public function wrapIdentifier(string $identifier, string $context): string; // Wrap identifier in quotes
    
    /**
     * @param mixed[] ...$args One or more values
     * @return Raw
     * @param mixed $args
     */
    public function raw(...$args): Raw
    {// 2023-05-09
        $iRaw = new Raw($this);
        $iRaw->set(...$args);

        return $iRaw;
    }

    public function getQueryCompiler(QueryBuilder $iQueryBuilder): QueryCompiler
    {// 2023-05-10
        return new QueryCompiler($this, $iQueryBuilder, []);
    }
}
