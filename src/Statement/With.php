<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;

class With implements IStatement, IAliasable
{
    use TStatement;

    public const TYPE_WRAPPED                  = 'WITH_WRAPPED';
    public const TYPE_RECURSIVE_WRAPPED        = 'WITH_RECURSIVE_WRAPPED';
    public const TYPE_MATERIALIZED_WRAPPED     = 'WITH_MATERIALIZED_WRAPPED';
    public const TYPE_NOT_MATERIALIZED_WRAPPED = 'WITH_NOT_MATERIALIZED_WRAPPED';

    private string $type;

    private string $alias;
    private ?array  $columnList;
    private $value;

    public function __construct(string $type, string $alias, ?array $columnList, $value = null)
    {// 2023-06-07
        $this->as($alias);

        $this->type       = $type;
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

    public function isRecursive(): bool
    {// 2023-05-10
        return $this->type === self::TYPE_RECURSIVE_WRAPPED;
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

    public function getValue()
    {// 2023-06-07
        return $this->value;
    }

    public function getColumns(): ?array
    {// 2023-06-07
        return $this->columnList;
    }
}
