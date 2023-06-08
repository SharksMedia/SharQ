<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class Order implements IStatement
{
    public const TYPE_BASIC = 'ORDER_BY_BASIC';
    public const TYPE_RAW = 'ORDER_BY_RAW';

    public const TYPE_NULLS_POSITION_FIRST = 'NULLS_POSITION_FIRST';
    public const TYPE_NULLS_POSITION_LAST = 'NULLS_POSITION_LAST';

    public const DIRECTION_ASC = 'ASC';
    public const DIRECTION_DESC = 'DESC';
    
    private string $type;
    private        $column;
    private ?string $direction;
    private ?string $nullsPosition;

    public function getClass(): string
    {// 2023-05-10
        return 'Order';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'orderByBasic',
            'orderByRaw',
        ];

        return $types;
    }

    public function __construct(string $type, $column, $direction, $nullsPosition=null)
    {// 2023-05-08
        $this->type = $type;
        $this->column = $column;
        $this->direction = $direction;
        $this->nullsPosition = $nullsPosition;
    }
    
    public function getColumn()
    {// 2023-05-08
        return $this->column;
    }

    public function getDirection()
    {// 2023-05-08
        return $this->direction;
    }

    public function hasDirection(): bool
    {// 2023-06-05
        return $this->direction !== null;
    }

    public function getNullsPosition()
    {// 2023-05-08
        return $this->nullsPosition;
    }

}
