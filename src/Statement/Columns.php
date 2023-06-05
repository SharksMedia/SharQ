<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;
use Sharksmedia\QueryBuilder\Statement\IAliasable;

/**
 * 2023-05-08
 * @property array<int,string> $columns
 * @property bool $distinct
 * @property bool $distinctOn
 * @property string|null $as Alias
 */
class Columns implements IStatement, IAliasable
{
    public const TYPE_PLUCK = 'COLUMNS_PLUCK';
    public const TYPE_FIRST = 'COLUMNS_FIRST';
    public const TYPE_RAW = 'COLUMNS_RAW';
    public const TYPE_ANALYTIC = 'COLUMNS_ANALYTIC';
    public const TYPE_AGGREGATE = 'COLUMNS_AGGREGATE';
    public const TYPE_AGGREGATE_RAW = 'COLUMNS_AGGREGATE_RAW';

    public const AGGREGATE_FUNCTION_COUNT = 'COUNT';

    private string $type;

    private array $columns;
    private bool $distinct = false;
    private bool $distinctOn = false;

    private ?string $aggregateFunction = null;

    // Singles
    private ?string $as = null;

    /**
     * @param array<int,string|Raw> $columns
     */
    public function __construct(?string $aggregateFunction, array $columns, string $type=self::TYPE_PLUCK)
    {// 2023-05-08
        $this->aggregateFunction = $aggregateFunction;
        $this->columns = $columns;
        $this->type = $type;
    }

    public function getClass(): string
    {// 2023-05-08
        return 'Columns';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'pluck',
            'analytic',
            'aggregate',
            'aggregateRaw',
        ];

        return $types;
    }

    public function getAggregateFunction(): ?string
    {// 2023-05-08
        return $this->aggregateFunction;
    }

    /**
     * @return array<int,string|callable|Raw>
     */
    public function getColumns(): array
    {// 2023-05-10
        return $this->columns;
    }

    public function setColumns(array $columns): self
    {// 2023-05-10
        $this->columns = $columns;

        return $this;
    }

    public function isSingleColumn(): bool
    {// 2023-05-26
        if(count($this->columns) > 1) return false;

        // $column = $this->columns[0];
        $column = reset($this->columns);

        if(is_array($column)) return count($column) === 1;

        return true;
    }

    public function hasAlias(): bool
    {// 2023-05-10
        return (string)$this->as !== '';
    }

    public function getAlias(): ?string
    {// 2023-05-10
        return $this->as;
    }

    public function as(?string $alias): self
    {// 2023-05-08
        $this->as = $alias;

        return $this;
    }

    /**
     * 2023-05-08
     * @param bool $distinct
     * @return self
     */
    public function distinct(bool $distinct): self
    {// 2023-05-08
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * 2023-05-08
     * @return bool
     */
    public function isDistinct(): bool
    {// 2023-05-08
        return $this->distinct;
    }

    /**
     * 2023-05-08
     * @param bool $distinctOn
     * @return self
     */
    public function distinctOn(bool $distinctOn): self
    {// 2023-05-08
        $this->distinctOn = $distinctOn;

        return $this;
    }

    /**
     * 2023-05-08
     * @return bool
     */
    public function isDistinctOn(): bool
    {// 2023-05-08
        return $this->distinctOn;
    }
}
