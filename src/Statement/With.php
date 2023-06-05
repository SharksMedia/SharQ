<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class With implements IStatement
{
    public const TYPE_WRAPPED = 'WITH_WRAPPED';

    private string $type;
    private bool   $isReqursive = false;

    public function getClass(): string
    {// 2023-05-10
        return 'With';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'withWrapped',
        ];

        return $types;
    }

    public function isRecursive(): bool
    {// 2023-05-10
        return $this->isReqursive;
    }
}
