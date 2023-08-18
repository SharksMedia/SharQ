<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;

class Union implements IStatement
{
    public const TYPE_BASIC             = 'UNION_BASIC';
    public const TYPE_INTERSECT         = 'UNION_INTERSECT';
    public const TYPE_ALL               = 'UNION_ALL';

    private string      $type;
    private             $statement;
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
    /**
     * @param \Closure|\Sharksmedia\SharQ\SharQ $statement
     */
    public function __construct(string $type, $statement, bool $wrap)
    {// 2023-05-08
        $this->type = $type;
        $this->statement = $statement;
        $this->wrap = $wrap;
    }

    /**
     * @return \Closure|\Sharksmedia\SharQ\SharQ
     */
    public function getStatement()
    {// 2023-05-08
        return $this->statement;
    }

    public function isWrapping(): bool
    {// 2023-05-08
        return $this->wrap;
    }

    public function getClause(): string
    {// 2023-06-07
        if($this->getType() === self::TYPE_BASIC) return 'UNION';
        if($this->getType() === self::TYPE_ALL) return 'UNION ALL';
        if($this->getType() === self::TYPE_INTERSECT) return 'INTERSECT';

        throw new \Exception('Invalid union type "' . $this->getType() . '"');
    }

}
