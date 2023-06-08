<?php

/**
 * 2023-06-07
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;

/**
 * 2023-05-08
 * @property array<int,string> $hintComments
 */
class Comments implements IStatement
{
    public const TYPE_BASIC = 'BASIC';

    private array $comments;

    /**
     * @param array<int,string> $comments
     */
    public function __construct(array $comments)
    {// 2023-05-08
        $this->comments = $comments;
    }

    public function getClass(): string
    {// 2023-05-08
        return 'Comments';
    }

    public function getType(): string
    {// 2023-05-08
        return 'Comments';
    }

    /**
     * @return array<int,string>
     */
    public function getComments(): array
    {// 2023-05-15
        return $this->comments;
    }
}
