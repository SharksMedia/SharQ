<?php

/**
 * 2023-05-08
 * Represents a compiled query
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class Query
{
    private string $sql;
    private array  $bindings = [];

    private string        $method;
    private array         $options;
    private int           $timeout;
    private bool          $cancelOnTimeout;
    private string        $queryUUID;

    private array  $aliases = [];

    public function __construct(string $method, array $options, int $timeout, bool $cancelOnTimeout, array $bindings, string $queryUUID)
    {// 2023-05-15
        $this->method          = $method;
        $this->options         = $options;
        $this->timeout         = $timeout;
        $this->cancelOnTimeout = $cancelOnTimeout;
        $this->bindings        = $bindings;

        $this->queryUUID = $queryUUID;
    }

    public function getSQL(): string
    {// 2023-05-10
        return $this->sql;
    }

    public function setSQL(string $sql): void
    {// 2023-05-10
        $this->sql = $sql;
    }

    public function getAs(): string
    {// 2023-05-15
        return $this->aliases[0];
    }

    public function hasAs(): bool
    {// 2023-05-10
        return count($this->aliases) !== 0;
    }

    public function as(string $alias): void
    {// 2023-05-10
        $this->aliases[] = $alias;
    }

    public function getMethod(): string
    {// 2023-05-10
        return $this->method;
    }

    public function toString(bool $isParameter, SharQCompiler $iSharQCompiler): string
    {// 2023-05-15
        $sql = $this->getSQL();

        if (in_array($this->getMethod(), [SharQ::METHOD_SELECT, SharQ::METHOD_FIRST, SharQ::METHOD_UPDATE, SharQ::METHOD_DELETE]) && ($isParameter || $this->hasAs()))
        {
            $sql = "({$sql})";

            if ($this->hasAs())
            {
                $sql = $sql.' AS '.$iSharQCompiler->wrap($this->getAs());
            }
        }

        return $sql;
    }

    public function setBindings(array $bindings): void
    {// 2023-05-16
        $this->bindings = $bindings;
    }

    public function getBindings(): array
    {// 2023-05-16
        return $this->bindings;
    }
}
