<?php

/*
columns
    pluck
    json
    analytic
    aggregate
    aggregateRaw
hintComments
where
    whereBasic
    whereRaw
    whereWrapped
    whereExists
    whereIn
    whereNull
    whereBetween
group
    groupByBasic
    groupByRaw
order
    orderByBasic
    orderByRaw
union
    intersect
having
    havingBasic
    havingWrapped
    havingNull
    havingExists
    havingBetween
    havingIn
    havingRaw
with
    withWrapped
/*

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

interface IStatement
{
    /**
     * 2023-05-08
     * Returns the statement type as a string
     * @return string
     */
    public function getClass(): string;

    /**
     * 2023-05-08
     * Returns the statement type as a string
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $class 
     * @return void 
     */
    public function identify(string $class): void;
    public function getIdentifier(): ?string;
}

