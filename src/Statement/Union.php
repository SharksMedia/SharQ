<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class Union implements IStatement
{
    public const TYPE_BASIC             = 'UNION_BASIC';
    public const TYPE_INTERSECT         = 'UNION_INTERSECT';
    public const TYPE_ALL               = 'UNION_ALL';

    private string      $type;
    private             $value;
    private bool        $wrap;

    public function getClass(): string
    {// 2023-05-10
        return 'Union';
    }

    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            self::TYPE_BASIC,
            self::TYPE_INTERSECT,
        ];

        return $types;
    }

    public function __construct(string $type, callable $value, bool $wrap)
    {// 2023-05-08
        $this->type = $type;
        $this->value = $value;
        $this->wrap = $wrap;
    }

}
