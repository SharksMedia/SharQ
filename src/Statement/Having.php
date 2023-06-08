<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Statement\IStatement;

class Having implements IStatement
{
    public const TYPE_BASIC = 'HAVING_BASIC';
    public const TYPE_WRAPPED = 'HAVING_WRAPPED';
    public const TYPE_NULL = 'HAVING_NULL';
    public const TYPE_EXISTS = 'HAVING_EXISTS';
    public const TYPE_BETWEEN = 'HAVING_BETWEEN';
    public const TYPE_IN = 'HAVING_IN';
    public const TYPE_RAW = 'HAVING_RAW';

    private string $type;

    private         $column;
    private ?string $operator;
    private         $value;
    private string  $boolType;
    private bool    $isNot;

    public function getClass(): string
	{// 2023-05-10
        return 'Having';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'havingBasic',
            'havingWrapped',
            'havingNull',
            'havingExists',
            'havingBetween',
            'havingIn',
            'havingRaw',
        ];

        return $types;
    }

    /**
     * @param string|callable|Raw $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolType
     * @param bool $isNot
     */
    public function __construct(string $type, $column, ?string $operator, $value, string $boolType=QueryBuilder::BOOL_TYPE_AND, bool $isNot=false)
    {// 2023-05-08
        $this->column   = $column;
        $this->operator = $operator;
        $this->value    = $value;
        $this->boolType = $boolType;
        $this->isNot    = $isNot;
        $this->type     = $type;
    }

    public function getColumn()
    {// 2023-05-15
        return $this->column;
    }

    public function getOperator()
    {// 2023-05-15
        return $this->operator;
    }

    public function getValue()
    {// 2023-05-15
        return $this->value;
    }

    public function isNot(): bool
    {// 2023-06-01
        return $this->isNot;
    }

    public function getBoolType(): string
    {// 2023-05-15
        return $this->boolType;
    }

}
