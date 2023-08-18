<?php

/**
 * // 2023-05-10
 * Represents a compiled query
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class Utilities
{
    /**
     * @param array<int,mixed> $array
     */
    public static function arrayRemoveFalsey(array $array): array
    {// 2023-05-10
        return array_filter($array, function($value)
        {// 2023-05-10
            return (bool)$value;
        });
    }
}
