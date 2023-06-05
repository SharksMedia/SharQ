<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

/**
 * 2023-05-08
 * @property array<int,string> $hintComments
 */
class HintComments implements IStatement
{
    public const TYPE_RAW = 'HINT_COMMENT_RAW';

    private array $hintComments;

    /**
     * @param array<int,string> $hintComments
     */
    public function __construct(array $hintComments)
    {// 2023-05-08
        $this->hintComments = $hintComments;
    }

    public function getClass(): string
    {// 2023-05-08
        return 'HintComment';
    }

    public function getType(): string
    {// 2023-05-08
        return 'HintComment';
    }

    /**
     * @return array<int,string>
     */
    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'hintCommentRaw',
        ];

        return $types;
    }

    /**
     * @return array<int,string>
     */
    public function getComments(): array
    {// 2023-05-15
        return $this->hintComments;
    }
}
