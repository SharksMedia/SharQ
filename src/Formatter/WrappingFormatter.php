<?php

/**
 * 2023-05-10
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Formatter;

use Sharksmedia\QueryBuilder\Query;
use Sharksmedia\QueryBuilder\Client;
use Sharksmedia\QueryBuilder\Statement\Raw;
use Sharksmedia\QueryBuilder\QueryBuilder;


/**
 * 2023-05-08
 */
class WrappingFormatter
{
    public static function wrapString(string $str, QueryBuilder $iQueryBuilder, Client $iClient): string
    {// 2023-05-10
        $context = $iQueryBuilder->getContext();
        return $iClient->wrapIdentifier(trim($str), $context);
    }

    // Ensures the query is aliased if necessary.
    public static function outputQuery(Query $iQuery, $isParameter, QueryBuilder $iQueryBuilder, Client $iClient): ?string
    {// 2023-05-10
        $sql = $iQuery->getSQL();
        
        if($sql === null) return $sql;

        $isAliased = in_array($iQuery->getMethod(), ['select', 'first']) && ($isParameter || $iQuery->hasAlias());

        $sql = '('.$sql.')';

        if(!$isAliased) return $sql;

        if($iQuery->hasAs()) return $iClient->alias($sql, self::wrapString($iQuery->getAlias(), $iQueryBuilder, $iClient));

        return $sql;
    }

    /**
     * 2023-05-10
     * @param Raw|QueryBuilder $value
     * @param bool|null $isParameter
     * @param QueryBuilder $iBuilder
     * @param Client $iClient
     * @param array $bindingsHolder
     * @return string
     */
    public static function unwrapRaw($value, bool $isParameter, QueryBuilder $iBuilder, $iClient, $bindingsHolder): ?string
    {// 2023-05-10
        if($value instanceof QueryBuilder)
        {
            $iQueryCompiler = $iClient->getQueryCompiler($value);
            $iQuery = $iQueryCompiler->toSQL();

            if(count($iQuery->getBindings()) > 0) $bindingsHolder = array_merge($bindingsHolder, $iQuery->getBindings());
            
            return self::outputQuery($iQuery, $isParameter, $iBuilder, $iClient);
        }

        if($value instanceof Raw)
        {// 2023-05-10
            $iQuery = $value->toSQL();

            if(count($iQuery->getBindings()) > 0) $bindingsHolder = array_merge($bindingsHolder, $iQuery->getBindings());

            return $iQuery->getSQL();
        }

        if($isParameter)
        {// 2023-05-10
            $bindingsHolder[] = $value;
        }

        return null;
    }

    public static function fnOrRaw($value, $method, $builder, $client, $bindingHolder): ?string
    {// 2023-05-10
        if(!is_callable($value)) return self::unwrapRaw($value, null, builder, client, bindingHolder);
    }

    public static function columnize(array $columns, QueryBuilder $iBuilder, Client $iClient, array $bindings): string
    {// 2023-05-10
        $str = '';
        foreach($columns as $i=>$column)
        {
            if($i > 0) $str .= ', ';
            $str .= self::wrap($column, null, $iBuilder, $iClient, $bindings);
        }

        return $str;
    }

    public static function wrap($value, $isParameter, QueryBuilder $iBuilder, Client $iClient, array $bindings): ?string
    {// 2023-05-10
        if($value instanceof Raw) return self::unwrapRaw($value, $isParameter, $iBuilder, $iClient, $bindings);

        if(is_callable($value))
        {
            $compiledValue = self::compileCallback($value, null, $iClient, $bindings);

            return self::outputQuery($compiledValue, true, $iBuilder, $iClient);
        }

        if(is_object($value))
        {
            // FIXME: Implement me!
            throw new \Exception('Not implemented yet');
        }

        if(is_int($value) || is_float($value)) return (string)$value;

        return self::wrapString($value.'', $iBuilder, $iClient, $bindings);
    }
}
