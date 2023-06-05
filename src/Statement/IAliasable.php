<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

interface IAliasable
{
    /**
     * 2023-05-08
     * Returns the statement type as a string
     * @param string $alias
     * @return self
     */
    public function as(string $alias): self;
}

