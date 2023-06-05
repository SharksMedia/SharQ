<?php

/**
 * // 2023-05-09
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Client;
use Sharksmedia\QueryBuilder\Statement\IStatement;

/**
 * // 2023-05-09
 * @property Client $iClient
 * @property string $sql
 * @property array<string,mixed> $bindings
 */
class Raw implements IStatement
{
    public const TYPE_RAW = 'RAW_RAW';

    private string $type;

    private Client $iClient;

    private string $sql;
    private array $bindings;

    public function __construct($sql=null, ...$bindings)
    {// 2023-05-09
        if($sql !== null) $this->sql = (string)$sql;
        $this->bindings = $bindings;
        // $this->iClient = $iClient;
    }

    public function getClass(): string
    {// 2023-05-08
        return 'Raw';
    }
    
    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'raw',
        ];

        return $types;
    }

    public function getSQL(): string
    {// 2023-05-09
        return $this->sql;
    }

    /**
     * @return array<string,mixed>
     */
    public function getBindings(): array
    {// 2023-05-09
        return $this->bindings;
    }

    /**
     * @param string $sql
     * @param array<string,mixed> $bindings
     * @return self
     */
    public function set(string $sql, array $bindings): self
    {// 2023-05-09
        $this->sql = $sql;
        $this->bindings = $bindings;

        return $this;
    }
}
