<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class With implements IStatement, IAliasable
{
    public const TYPE_WRAPPED = 'WITH_WRAPPED';

    private string $type;
    private bool   $isReqursive = false;

    private string $alias;
    private ?array  $columnList;
    private $value;

    public function __construct(string $alias, ?array $columnList, $value=null)
    {// 2023-06-07
        $this->as($alias);

        $this->columnList = $columnList;
        $this->value      = $value;
    }

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

    public function as(string $alias): IAliasable
    {// 2023-06-07
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {// 2023-06-07
        return $this->alias;
    }
}
