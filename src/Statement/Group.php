<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;

class Group implements IStatement
{
    public const TYPE_BASIC = 'GROUP_BY_BASIC';
    public const TYPE_RAW   = 'GROUP_BY_RAW';

    /**
     * This is the type property.
     * @var string
     */
    private string $type;

    /**
     * This is the column property.
     * @var string|Raw|array<int, string|Raw>
     */
    private $column;

    /**
     * This method returns the class name.
     * @return string
     */
    public function getClass(): string
    {// 2023-05-10
        return 'Group';
    }

    /**
     * This method returns the type property.
     * @return string
     */
    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    /**
     * This method returns the column property.
     * @param string $type
     * @param array<int, string|Raw> $column
     */
    public function __construct(string $type, $column)
    {// 2023-05-08
        $this->type   = $type;
        $this->column = $column;
    }

    /**
     * This method returns the column property.
     * @return string|Raw|array<int, string|Raw>
     */
    public function getColumn()
    {// 2023-05-08
        return $this->column;
    }
}
