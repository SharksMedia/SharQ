<?php

/**
 * // 2023-05-09
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\Clause;

class Join implements IStatement, IAliasable
{
    public const TYPE_RAW               = 'JOIN_RAW';
    public const TYPE_INNER             = 'JOIN_INNER';
    public const TYPE_OUTER             = 'JOIN_OUTER';
    public const TYPE_CROSS             = 'JOIN_CROSS';
    public const TYPE_LEFT              = 'JOIN_LEFT';
    public const TYPE_LEFT_OUTER        = 'JOIN_LEFT_OUTER';
    public const TYPE_RIGHT             = 'JOIN_RIGHT';
    public const TYPE_RIGHT_OUTER       = 'JOIN_RIGHT_OUTER';
    public const TYPE_FULL_OUTER        = 'TYPE_FULL_OUTER';

    public const BOOL_AND               = 'AND';
    public const BOOL_OR                = 'OR';

    public const ON_TYPE_RAW            = 'ON_TYPE_RAW';
    public const ON_TYPE_BASIC          = 'ON_TYPE_BASIC';
    public const ON_TYPE_VALUE          = 'ON_TYPE_VALUE';
    public const ON_TYPE_BETWEEN        = 'ON_TYPE_BETWEEN';
    public const ON_TYPE_WRAPPED        = 'ON_TYPE_WRAPPED';
    public const ON_TYPE_USING          = 'ON_TYPE_USING';
    public const ON_TYPE_IN             = 'ON_TYPE_IN';
    public const ON_TYPE_NULL           = 'ON_TYPE_NULL';
    public const ON_TYPE_EXISTS         = 'ON_TYPE_EXISTS';

    public const ON_AND                 = 'ON_AND';
    public const ON_OR                  = 'ON_OR';

    /**
     * This is the iClient property.
     * @var string|Raw
     */
    private $table;

    /**
     * This is the iClient property.
     * @var string|Raw
     */
    private $alias;

    /**
     * This is the iClient property.
     * @var string Join::TYPE_* constants
     */
    private string $joinType;

    /**
     * This is the iClient property.
     * @var string SharQ::BOOL_TYPE_* constants
     */
    private string $boolType;

    /**
     * This is the iClient property.
     * @var array<int, Clause>
     */
    private array $iClauses = [];

    /**
     * @param string|Raw $table
     * @param string $joinType Join::TYPE_* constants
     */
    public function __construct($table, string $joinType)
    {// 2023-05-09
        $this->table    = $table;
        $this->joinType = $joinType;

        $this->boolType = self::ON_AND;
    }

    /**
     * Get class name.
     * @return string
     */
    public function getClass(): string
    {// 2023-05-10
        return 'Join';
    }

    /**
     * Get the type.
     * @return string
     */
    public function getType(): string
    {// 2023-05-09
        return $this->joinType;
    }

    /**
     * Get the table name.
     * @return string|Raw
     */
    public function getTableName()
    {// 2023-06-01
        return $this->table;
    }

    /**
     * @param string $joinType
     * @return Join
     */
    public function joinType(string $joinType): Join
    {// 2023-05-09
        $this->joinType = $joinType;

        return $this;
    }

    /**
     * @return string
     */
    public function getJoinFunction(): string
    {// 2023-05-31
        if ($this->joinType === self::TYPE_INNER)
        {
            return 'INNER JOIN';
        }

        if ($this->joinType === self::TYPE_OUTER)
        {
            return 'OUTER JOIN';
        }

        if ($this->joinType === self::TYPE_CROSS)
        {
            return 'CROSS JOIN';
        }

        if ($this->joinType === self::TYPE_LEFT)
        {
            return 'LEFT JOIN';
        }

        if ($this->joinType === self::TYPE_LEFT_OUTER)
        {
            return 'LEFT OUTER JOIN';
        }

        if ($this->joinType === self::TYPE_RIGHT)
        {
            return 'RIGHT JOIN';
        }

        if ($this->joinType === self::TYPE_RIGHT_OUTER)
        {
            return 'RIGHT OUTER JOIN';
        }

        throw new \Exception('Unknown join type: '.$this->joinType);
    }

    /**
     * @return Clause[]
     */
    public function getClauses(): array
    {// 2023-05-31
        return $this->iClauses;
    }

    /**
     * @param string $onType Join::ON_TYPE_* constants
     * @param string $boolType SharQ::BOOL_TYPE_* constants
     * @param array<int, string|Raw|SharQ> $args [first, $operator, $second]
     * @return Clause
     */
    private function getClauseFromArguments(string $onType, string $boolType, ...$args): Clause
    {// 2023-05-09
        $first    = $args[0] ?? null;
        $operator = $args[1] ?? null;
        $second   = $args[2] ?? null;

        if ($first instanceof \Closure)
        {
            $iClause           = new Clause();
            $iClause->type     = self::ON_TYPE_WRAPPED;
            $iClause->value    = $first;
            $iClause->boolType = $boolType;

            return $iClause;
        }

        $argCount = func_num_args();

        if ($argCount === 3)
        {
            $iClause           = new Clause();
            $iClause->type     = self::ON_TYPE_RAW;
            $iClause->value    = $first;
            $iClause->boolType = $boolType;

            return $iClause;
        }

        if ($argCount === 4)
        {
            $iClause              = new Clause();
            $iClause->type        = is_numeric($operator) ? self::ON_TYPE_VALUE : $onType;
            $iClause->columnFirst = $first;
            $iClause->operator    = '=';
            $iClause->value       = $operator;
            $iClause->boolType    = $boolType;

            return $iClause;
        }

        $iClause              = new Clause();
        $iClause->type        = is_numeric($second) ? self::ON_TYPE_VALUE : $onType;
        $iClause->columnFirst = $first;
        $iClause->operator    = $operator;
        $iClause->value       = $second;
        $iClause->boolType    = $boolType;

        return $iClause;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function on($first, ...$args): Join
    {// 2023-05-09
        $iClause = $this->getClauseFromArguments(self::ON_TYPE_BASIC, $this->boolType, $first, ...$args);

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function andOn($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;
        $this->on($first, ...$args);
    
        return $this;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function orOn($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        $this->on($first, ...$args);
    
        return $this;
    }

    /**
     * @param string|Raw $column
     * @return Join
     */
    public function using($column): Join
    {// 2023-05-09
        $iClause           = new Clause();
        $iClause->type     = self::ON_TYPE_USING;
        $iClause->column   = $column;
        $iClause->boolType = $this->boolType;

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function onVal($first, ...$args): Join
    {// 2023-05-09
        $iClause = $this->getClauseFromArguments(self::ON_TYPE_VALUE, $this->boolType, $first, ...$args);

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function andOnVal($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;
        $this->onVal($first, ...$args);

        return $this;
    }

    /**
     * @param string|Raw|\Closure $first
     * @param array<int, string|Raw|SharQ|\Closure> $args [operator, $second]
     * @return Join
     */
    public function orOnVal($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        $this->onVal($first, ...$args);

        return $this;
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw> $values
     * @param bool $isNot
     * @return Join
     */
    public function onBetween(string $column, array $values, bool $isNot = false): Join
    {// 2023-05-09
        if (count($values) !== 2)
        {
            throw new \UnexpectedValueException('Between clause must have 2 values');
        }
        
        $iClause           = new Clause();
        $iClause->type     = self::ON_TYPE_BETWEEN; 
        $iClause->column   = $column;
        $iClause->value    = $values;
        $iClause->boolType = $this->boolType;
        $iClause->isNot    = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw> $values
     * @return Join
     */
    public function onNotBetween(string $column, array $values): Join
    {// 2023-05-09
        return $this->onBetween($column, $values, true);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw> $values
     * @return Join
     */
    public function orOnBetween(string $column, array $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onBetween($column, $values, false);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw> $values
     * @return Join
     */
    public function orOnNotBetween(string $column, array $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onBetween($column, $values, true);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @param bool $isNot
     * @return Join
     */
    public function onIn(string $column, $values, bool $isNot = false): Join
    {// 2023-05-09
        if (is_array($values) && count($values) === 0)
        {
            return $this->on(new Raw(1), '=', new Raw(0));
        } // Mathes an empty array; will always be false.

        $iClause           = new Clause();
        $iClause->type     = self::ON_TYPE_IN;
        $iClause->column   = $column;
        $iClause->value    = $values;
        $iClause->boolType = $this->boolType;
        $iClause->isNot    = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @return Join
     */
    public function onNotIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;

        return $this->onIn($column, $values, true);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @return Join
     */
    public function andOnIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;

        return $this->onIn($column, $values, false);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @return Join
     */
    public function andOnNotIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;

        return $this->onIn($column, $values, true);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @return Join
     */
    public function orOnIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onIn($column, $values, false);
    }

    /**
     * @param string $column
     * @param array<int, int|float|string|Raw>|SharQ $values
     * @return Join
     */
    public function orOnNotIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onIn($column, $values, true);
    }

    /**
     * @param string $column
     * @param bool $isNot
     * @return Join
     */
    public function onNull(string $column, bool $isNot = false): self
    {// 2023-05-09
        $iClause           = new Clause();
        $iClause->type     = self::ON_TYPE_NULL;
        $iClause->column   = $column;
        $iClause->boolType = $this->boolType;
        $iClause->isNot    = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param string $column
     * @return Join
     */
    public function onNotNull(string $column): self
    {// 2023-05-09
        return $this->onNull($column, true);
    }

    /**
     * @param string $column
     * @return Join
     */
    public function orOnNull(string $column): self
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onNull($column, false);
    }

    /**
     * @param string $column
     * @return Join
     */
    public function orOnNotNull(string $column): self
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onNull($column, true);
    }

    /**
     * @param \Closure $callback
     * @param bool $isNot
     * @return Join
     */
    public function onExists(\Closure $callback, bool $isNot = false): Join
    {// 2023-05-09
        $iClause           = new Clause();
        $iClause->type     = self::ON_TYPE_EXISTS;
        $iClause->value    = $callback;
        $iClause->boolType = $this->boolType;
        $iClause->isNot    = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return Join
     */
    public function onNotExists(\Closure $callback): Join
    {// 2023-05-09
        return $this->onExists($callback, true);
    }

    /**
     * @param \Closure $callback
     * @return Join
     */
    public function orOnExists(\Closure $callback): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onExists($callback, false);
    }

    /**
     * @param \Closure $callback
     * @return Join
     */
    public function orOnNotExists(\Closure $callback): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;

        return $this->onExists($callback, true);
    }

    public function as(string $alias): IAliasable
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
