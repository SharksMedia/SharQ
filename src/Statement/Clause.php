<?php

/**
 * // 2023-05-09
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

/**
 * // 2023-05-09
 * This class is used for join clauses
 */
class Clause
{
    /**
     * This is the type property.
     * @see Join::ON_TYPE_* constants
     * @var string
     */
    public string           $type;

    /**
     * This is the type columnFirst.
     * @var string
     */
    public                  $columnFirst;

    /**
     * This is the type value.
     * @var string|Raw|QueryBuilder|\Closure
     */
    public                  $value;

    /**
     * This is the type operator.
     * @var string|null
     */
    public                  $operator;

    /**
     * This is the type boolType.
     * @see QueryBuilder::BOOL_TYPE_* constants
     * @var string
     */
    public string           $boolType;

    /**
     * This is the type isNot.
     * @var bool
     */
    public bool             $isNot;

    /**
     * This is the type column.
     * @var string|Raw
     */
    public                  $column;

    /**
     * This is the type columnSecond.
     * @see QueryBuilder::BOOL_TYPE_* constants
     * @return string
     */
    public function getBoolFunction(): string
    {// 2023-05-31
        if($this->boolType === Join::ON_AND) return 'AND';
        if($this->boolType === Join::ON_OR) return 'OR';

        throw new \Exception('Unknown bool type: ' . $this->boolType);
    }

    /**
     * This is ON function used in the clause
     * @see Join::ON_TYPE_* constants
     * @return string
     */
    public function getOnFunction(): string
    {// 2023-05-31
        if($this->type === Join::ON_TYPE_BASIC) return 'ON';
        if($this->type === Join::ON_TYPE_VALUE) return 'ON';
        if($this->type === Join::ON_TYPE_USING) return 'USING';
        if($this->type === Join::ON_TYPE_WRAPPED) return 'ON';
        if($this->type === Join::ON_TYPE_RAW) return 'ON';

        throw new \Exception('Unknown on type: ' . $this->type);
    }

    /**
     * Get the type of the clause
     * @see Join::ON_TYPE_* constants
     * @return string
     */
    public function getType(): string
    {// 2023-06-01
        return $this->type;
    }

    /**
     * Get the column of the clause
     * @return string|Raw
     */
    public function getColumn()
    {// 2023-05-31
        return $this->columnFirst ?? $this->column;
    }
 
    /**
     * Get the operator of the clause
     * @return string|null
     */
    public function getOperator()
    {// 2023-05-31
        return $this->operator;
    }

    /**
     * Get the value of the clause
     * @return string|Raw|QueryBuilder|\Closure
     */
    public function getValue()
    {// 2023-05-31
        return $this->value;
    }

    /**
     * Get if the clause is is not
     * @return bool
     */
    public function isNot(): bool
    {// 2023-05-31
        return $this->isNot;
    }
}
