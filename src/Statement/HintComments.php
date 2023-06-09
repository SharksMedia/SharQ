<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

class HintComments implements IStatement
{
    public const TYPE_RAW = 'HINT_COMMENT_RAW';

    /**
     * This is the type property.
     * @var array<int, string>
     */
    private array $hintComments;

    /**
     * @param array<int, string> $hintComments
     */
    public function __construct(array $hintComments)
    {// 2023-05-08
        $this->hintComments = $hintComments;
    }

    /**
     * This method returns the class name.
     * @return string
     */
    public function getClass(): string
    {// 2023-05-08
        return 'HintComments';
    }

    /**
     * This method returns the type property.
     * @return string
     */
    public function getType(): string
    {// 2023-05-08
        return 'HintComments';
    }

    /**
     * @return array<int,string>
     */
    public function getComments(): array
    {// 2023-05-15
        return $this->hintComments;
    }
}
