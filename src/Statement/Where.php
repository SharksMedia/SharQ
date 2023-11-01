<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Statement\IStatement;
use Sharksmedia\SharQ\Statement\IAliasable;

/**
 * 2023-05-08
 * @property string|\Closure $column
 * @property string $operator
 * @property mixed $value
 * @property string $boolType
 * @property bool $isNot
 * @property string|null $as
 */
class Where implements IStatement, IAliasable
{
    use TStatement;

    public const TYPE_BASIC         = 'WHERE_BASIC';
    public const TYPE_COLUMN        = 'WHERE_COLUMN';
    public const TYPE_LIKE          = 'WHERE_LIKE';
    public const TYPE_ILIKE         = 'WHERE_ILIKE';
    public const TYPE_RAW           = 'WHERE_RAW';
    public const TYPE_WRAPPED       = 'WHERE_WRAPPED';
    public const TYPE_EXISTS        = 'WHERE_EXISTS';
    public const TYPE_IN            = 'WHERE_IN';
    public const TYPE_NULL          = 'WHERE_NULL';
    public const TYPE_BETWEEN       = 'WHERE_BETWEEN';

    public const BOOL_TYPE_AND = 'AND';
    public const BOOL_TYPE_OR  = 'OR';

    private string $type;

    private $column;
    private ?string $operator;
    private $value;
    private string  $boolType;
    private bool    $isNot;

    private ?string $as = null;

    /**
     * @param string|\Closure|Raw $column
     * @param string $operator
     * @param mixed $value
     * @param string $boolType
     * @param bool $isNot
     */
    public function __construct($column, ?string $operator, $value, string $boolType = SharQ::BOOL_TYPE_AND, bool $isNot = false, string $type = self::TYPE_BASIC)
    {// 2023-05-08
        $this->column   = $column;
        $this->operator = $operator;
        $this->value    = $value;
        $this->boolType = $boolType;
        $this->isNot    = $isNot;
        $this->type     = $type;
    }

    public function getClass(): string
    {// 2023-05-10
        return 'Where';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'whereBasic',
            'whereRaw',
            'whereWrapped',
            'whereExists',
            'whereIn',
            'whereNull',
            'whereBetween',
        ];

        return $types;
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
    
    public function as(string $alias): self
    {// 2023-05-08
        $this->as = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {// 2023-06-15
        return $this->as;
    }

    public function getBoolType(): string
    {// 2023-05-15
        return $this->boolType;
    }
}
