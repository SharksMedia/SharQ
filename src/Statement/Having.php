<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

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

}
