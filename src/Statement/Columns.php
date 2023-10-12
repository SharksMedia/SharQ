<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;
use Sharksmedia\SharQ\Statement\IAliasable;

/**
 * 2023-05-08
 * @property array<int,string> $columns
 * @property bool $distinct
 * @property bool $distinctOn
 * @property string|null $as Alias
 */
class Columns implements IStatement, IAliasable
{
    public const TYPE_PLUCK         = 'COLUMNS_PLUCK';
    public const TYPE_FIRST         = 'COLUMNS_FIRST';
    public const TYPE_RAW           = 'COLUMNS_RAW';
    public const TYPE_ANALYTIC      = 'COLUMNS_ANALYTIC';
    public const TYPE_AGGREGATE     = 'COLUMNS_AGGREGATE';
    public const TYPE_AGGREGATE_RAW = 'COLUMNS_AGGREGATE_RAW';

    public const AGGREGATE_FUNCTION_COUNT = 'COUNT';

    /**
     * This is the type property.
     * @see Columns::TYPE_* constants
     * @var string Columns::TYPE_* constants
     */
    private string $type;

    /**
     * This is the columns property.
     * @var array<int|string, string|Raw|SharQ>
     */
    private array $columns;

    /**
     * This is the distinct property.
     * @var bool
     */
    private bool $distinct = false;

    /**
     * This is the distinctOn property.
     * @var bool
     */
    private bool $distinctOn = false;

    /**
     * This is the aggregateFunction property.
     * @var string|null
     */
    private ?string $aggregateFunction = null;

    /**
     * This is the alias property.
     * @var string|null
     */
    private ?string $as = null;

    /**
     * @see Columns::TYPE_* constants
     * @param string|null $aggregateFunction eg: COUNT, SUM, AVG, MIN, MAX
     * @param array<int,string|Raw> $columns
     * @param string $type Columns::TYPE_* constants
     */
    public function __construct(?string $aggregateFunction, array $columns, string $type = self::TYPE_PLUCK)
    {// 2023-05-08
        $this->aggregateFunction = $aggregateFunction;
        $this->columns           = $columns;
        $this->type              = $type;
    }

    /**
     * get the class name
     * @return string
     */
    public function getClass(): string
    {// 2023-05-08
        return 'Columns';
    }

    /**
     * get the type
     * @return string
     */
    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    /**
     * get the aggregate function
     * @return string|null
     */
    public function getAggregateFunction(): ?string
    {// 2023-05-08
        return $this->aggregateFunction;
    }

    /**
     * get the columns
     * @return array<int|string, string|\Closure|Raw|SharQ>
     */
    public function getColumns(): array
    {// 2023-05-10
        return $this->columns;
    }

    /**
     * set the columns
     * @param array<int|string, string|\Closure|Raw|SharQ> $columns
     * @return Columns
     */
    public function setColumns(array $columns): self
    {// 2023-05-10
        $this->columns = $columns;

        return $this;
    }

    /**
     * Is the columns a single column
     * @return bool
     */
    public function isSingleColumn(): bool
    {// 2023-05-26
        if (count($this->columns) > 1)
        {
            return false;
        }

        // $column = $this->columns[0];
        $column = reset($this->columns);

        if (is_array($column))
        {
            return count($column) === 1;
        }

        return true;
    }

    /**
     * Has alias
     * @return bool
     */
    public function hasAlias(): bool
    {// 2023-05-10
        return (string)$this->as !== '';
    }

    /**
     * Get alias
     * @return string|null
     */
    public function getAlias(): ?string
    {// 2023-05-10
        return $this->as;
    }

    /**
     * Set alias
     * @paran string|null $alias
     * @return Columns
     */
    public function as(?string $alias): self
    {// 2023-05-08
        $this->as = $alias;

        return $this;
    }

    /**
     * Distinct
     * @param bool $distinct
     * @return Columns
     */
    public function distinct(bool $distinct): self
    {// 2023-05-08
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Is distinct
     * @return bool
     */
    public function isDistinct(): bool
    {// 2023-05-08
        return $this->distinct;
    }

    /**
     * Distinct on
     * @param bool $distinctOn
     * @return Columns
     */
    public function distinctOn(bool $distinctOn): self
    {// 2023-05-08
        $this->distinctOn = $distinctOn;

        return $this;
    }

    /**
     * Is distinct on
     * @return bool
     */
    public function isDistinctOn(): bool
    {// 2023-05-08
        return $this->distinctOn;
    }
}
