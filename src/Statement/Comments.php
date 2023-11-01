<?php

/**
 * 2023-06-07
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;

class Comments implements IStatement
{
    use TStatement;

    public const TYPE_BASIC = 'BASIC';

    /**
     * This is the type property.
     * @var array<int, string>
     */
    private array $comments;

    /**
     * @param array<int, string> $comments
     */
    public function __construct(array $comments)
    {// 2023-05-08
        $this->comments = $comments;
    }

    /**
     * Get the class
     * @return string
     */
    public function getClass(): string
    {// 2023-05-08
        return 'Comments';
    }

    /**
     * Get the type
     * @return string
     */
    public function getType(): string
    {// 2023-05-08
        return 'Comments';
    }

    /**
     * @return array<int, string>
     */
    public function getComments(): array
    {// 2023-05-15
        return $this->comments;
    }
}
