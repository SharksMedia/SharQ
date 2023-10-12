<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Statement\IStatement;

class Having implements IStatement
{
    public const TYPE_BASIC   = 'HAVING_BASIC';
    public const TYPE_WRAPPED = 'HAVING_WRAPPED';
    public const TYPE_NULL    = 'HAVING_NULL';
    public const TYPE_EXISTS  = 'HAVING_EXISTS';
    public const TYPE_BETWEEN = 'HAVING_BETWEEN';
    public const TYPE_IN      = 'HAVING_IN';
    public const TYPE_RAW     = 'HAVING_RAW';

    /**
     * This is the type property.
     * @var string
     */
    private string $type;

    /**
     * This is the column property.
     * @var string|Raw
     */
    private $column;

    /**
     * This is the operator property.
     * @var string|Raw|SharQ|\Closure|null
     */
    private ?string $operator;

    /**
     * This is the value property.
     * @var string|Raw|SharQ|\Closure|null
     */
    private $value;

    /**
     * This is the boolType property.
     * @see SharQ::BOOL_TYPE_* constants
     * @var string
     */
    private string  $boolType;

    /**
     * This is the isNot property.
     * @var bool
     */
    private bool    $isNot;

    /**
     * This method returns the class name.
     * @return string
     */
    public function getClass(): string
    {// 2023-05-10
        return 'Having';
    }

    /**
     * This method returns the type property.
     * @return string
     */
    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    /**
     * @param string|\Closure|Raw $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolType
     * @param bool $isNot
     */
    public function __construct(string $type, $column, ?string $operator, $value, string $boolType = SharQ::BOOL_TYPE_AND, bool $isNot = false)
    {// 2023-05-08
        $this->column   = $column;
        $this->operator = $operator;
        $this->value    = $value;
        $this->boolType = $boolType;
        $this->isNot    = $isNot;
        $this->type     = $type;
    }

    /**
     * This method returns the column property.
     * @return string|Raw
     */
    public function getColumn()
    {// 2023-05-15
        return $this->column;
    }

    /**
     * This method returns the column property.
     * @return string|Raw|SharQ|\Closure|null
     */
    public function getOperator()
    {// 2023-05-15
        return $this->operator;
    }

    /**
     * This method returns the column property.
     * @return string|Raw|SharQ|\Closure|null
     */
    public function getValue()
    {// 2023-05-15
        return $this->value;
    }

    /**
     * This method returns the column property.
     * @return bool
     */
    public function isNot(): bool
    {// 2023-06-01
        return $this->isNot;
    }

    /**
     * This method returns the column property.
     * @see SharQ::BOOL_TYPE_* constants
     * @return string
     */
    public function getBoolType(): string
    {// 2023-05-15
        return $this->boolType;
    }
}
