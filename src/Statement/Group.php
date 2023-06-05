<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class Group implements IStatement
{
    public const TYPE_BY_BASIC = 'GROUP_BY_BASIC';
    public const TYPE_BY_RAW = 'GROUP_BY_RAW';
    
    private string $type;

    public function getClass(): string
    {// 2023-05-10
        return 'Group';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'groupByBasic',
            'groupByRaw',
        ];

        return $types;
    }

}
