<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

use Sharksmedia\SharQ\Single;

use Sharksmedia\SharQ\Formatter\WrappingFormatter;
use Sharksmedia\SharQ\Statement\Columns;
use Sharksmedia\SharQ\Statement\Clause;
use Sharksmedia\SharQ\Statement\Having;
use Sharksmedia\SharQ\Statement\Join;
use Sharksmedia\SharQ\Statement\Where;
use Sharksmedia\SharQ\Statement\Order;
use Sharksmedia\SharQ\Statement\Raw;
use Sharksmedia\SharQ\Statement\With;
use Sharksmedia\SharQ\Utilities;

class SharQCompiler
{
    /**
     * This is the iClient property.
     * @var Client
     */
    private Client           $iClient;

    /**
     * This is the iSharQ property.
     * @var SharQ
     */
    private SharQ     $iSharQ;

    /**
     * This is the bindings property.
     * @var array<int, mixed>
     */
    private array            $bindings;

    /**
     * This is the iSingle property.
     * @var Single
     */
    private Single           $iSingle;

    /**
     * This is iStatementsGroupedOnType property.
     * @var array<string, array<int, Statement>>
     */
    private array $iStatementsGroupedOnType;

    /**
     * This is options property.
     * @var array<string, mixed>
     */
    private array $options = [];


    /**
     * This is timeout property.
     * @var int
     */
    private int $timeout = 0;

    /**
     * This is cancelOnTimeout property.
     * @var bool
     */
    private bool $cancelOnTimeout = false;

    public const QUERY_COMPONENTS =
    [
        'comments',
        'with',
        'columns',
        'join',
        'where',
        'union',
        'group',
        'having',
        'order',
        'limit',
        'offset',
        'lock',
        'waitMode',
    ];

    public const DELETE_COMPONENTS =
    [
        'comments',
        'with',
        'method',
        'join',
        'where',
        'order',
        'limit',
        'offset',
        'lock',
        'waitMode',
    ];

    public const UPDATE_COMPONENTS =
    [
        'comments',
        'with',
        'method',
        'join',
        'columns',
        'where',
        'order',
        'limit',
        'offset',
        'lock',
        'waitMode',
    ];

    public const INSERT_COMPONENTS =
    [
        'comments',
        'with',
        'method',
        'join',
        'columns',
        'lock',
        'waitMode',
    ];

    public const TRUNCATE_COMPONENTS =
    [
        'comments',
        'method',
        'waitMode',
    ];

    public const RAW_COMPONENTS =
    [
        'comments',
        'method',
        'waitMode',
    ];

    public const QUERY_OPERATORS =
    [
        '='           => '=',
        '<'           => '<',
        '>'           => '>',
        '<='          => '<=',
        '>='          => '>=',
        '<>'          => '<>',
        '<=>'         => '<=>',
        '!='          => '!=',
        'like'        => 'like',
        'not like'    => 'not like',
        'between'     => 'between',
        'not between' => 'not between',
        'ilike'       => 'ilike',
        'not ilike'   => 'not ilike',
        'exists'      => 'exists',
        'not exist'   => 'not exist',
        'rlike'       => 'rlike',
        'not rlike'   => 'not rlike',
        'regexp'      => 'regexp',
        'not regexp'  => 'not regexp',
        'match'       => 'match',
        '&'           => '&',
        '|'           => '|',
        '^'           => '^',
        '<<'          => '<<',
        '>>'          => '>>',
        '~'           => '~',
        '~='          => '~=',
        '~*'          => '~*',
        '!~'          => '!~',
        '!~*'         => '!~*',
        '#'           => '#',
        '&&'          => '&&',
        '@>'          => '@>',
        '<@'          => '<@',
        '||'          => '||',
        '&<'          => '&<',
        '&>'          => '&>',
        '-|-'         => '-|-',
        '@@'          => '@@',
        '!!'          => '!!',
    ];

    /**
     * @param Client $iClient
     * @param SharQ $iSharQ
     * @param array<int,mixed> $bindings
     */
    public function __construct(Client $iClient, SharQ $iSharQ, array $bindings = [])
    {// 2023-05-10
        $this->iClient  = $iClient;
        $this->iSharQ   = $iSharQ;
        $this->bindings = $bindings;

        $this->iSingle = $iSharQ->getSingle();

        // $this->iFormatter = $iClient->getFormatter();

        $this->iStatementsGroupedOnType = array_reduce($iSharQ->getStatements(), function($curr, $next)
        {
            $curr[$next->getClass()][] = $next;

            return $curr;
        }, []);
    }

    /**
     * Get bindings
     * @return array<int, mixed>
     */
    public function getBindings(): array
    {// 2023-05-16
        return $this->bindings;
    }

    /**
     * To SQL
     * @return Query
     */
    public function toQuery(?string $method = null): Query
    {// 2023-05-15
        $method = $method ?? $this->iSharQ->getMethod();

        // FIXME: Generate UUID
        $iQuery = new Query($method, $this->options, $this->timeout, $this->cancelOnTimeout, $this->bindings, '<UUID>');

        $value = $this->compileStatements($method);

        $iQuery->setSQL($value);
        $iQuery->setBindings($this->bindings);

        return $iQuery;
    }

    /**
     * Take a value and processes it to a string
     * @param string|Raw|SharQ|null $value
     * @param array<int, mixed> $bindings
     * @return string
     */
    private function unwrapRaw($value, array &$bindings): string
    {// 2023-05-10
        if ($value instanceof Raw)
        {
            $sql   = $value->getSQL();
            $binds = $value->getBindings();
            
            if ($sql instanceof Raw)
            {
                return $this->unwrapRaw($sql, $bindings);
            }
            
            $regex   = '/(\\?\\??|:\w+)/';
            $matches = [];
            $offset  = 0;

            while (preg_match($regex, $sql, $matches, PREG_OFFSET_CAPTURE, $offset ?? 0) === 1)
            {
                $matches = array_shift($matches);

                $match  = array_shift($matches);
                $offset = array_shift($matches);

                $bind = array_shift($binds);

                if ($match === '?')
                {
                    $compiledBind = ($bind instanceof Raw)
                        ? $this->unwrapRaw($bind, $bindings)
                        : $bind;

                    $bindings[] = $compiledBind;
                    $offset += strlen($match);
                }
                else if ($match[0] === ':')
                {
                    $compiledBind = ($bind instanceof Raw)
                        ? $this->unwrapRaw($bind, $bindings)
                        : $bind;

                    $sql = preg_replace('/'.preg_quote($match).'/', '?', $sql, 1, $count);

                    $bindings[] = $compiledBind;
                    $offset += strlen('?');
                }
                else
                {
                    if ($bind instanceof Raw)
                    {
                        $compiledBind = $this->unwrapRaw($bind, $bindings);
                    }
                    else
                    {
                        $compiledBind = is_array($bind)
                            ? $this->columnize($bind)
                            : $this->wrap($bind);
                    }

                    $sql = preg_replace('/'.preg_quote($match).'/', $compiledBind, $sql, 1, $count);
                    $offset += strlen($compiledBind);
                }
            }

            if ($value->isWrapped())
            {
                $sql = $value->getWrapBefore().$sql.$value->getWrapAfter();
            }

            return $sql;
        }

        if ($value instanceof SharQ)
        {
            $iSharQCompiler = new SharQCompiler($this->iClient, $value, []);

            $sql = $iSharQCompiler->toQuery()->toString(false, $this);

            $this->bindings = array_merge($this->bindings, $iSharQCompiler->getBindings());

            return $sql;
        }

        if ($value === null)
        {
            return 'NULL';
        }

        $bindings[] = $value;

        return '?';
    }

    /**
     * Wraps a identifier like a column name or table name in quotes appropriately.
     * @param string $identifier
     * @return string
     */
    private function wrapIdentifier(string $identifier): string
    {// 2023-05-10
        return $this->iClient->wrapIdentifier(trim($identifier), 'query');
    }

    /**
     * If we haven't specified any columns or a `tableName`, we're assuming this is only being used for unions.
     * @return bool
     */
    private function hasOnlyUnions(): bool
    {// 2023-05-10
        if (count($this->iStatementsGroupedOnType['Columns'] ?? []) !== 0)
        {
            return false;
        }

        if (count($this->iStatementsGroupedOnType['Union'] ?? []) === 0)
        {
            return false;
        }

        if ($this->iSingle->table !== null)
        {
            return false;
        }
        // if($this->iSharQ->getTableName() !== null) return false;
        
        return true;
    }

    /**
     * @param \Closure $callback
     * @param string|null $method SharQ::TYPE_* constant
     * @param Client|null $iClient
     * @param array<int,mixed> $bindings
     */
    private function compileCallback(\Closure $callback, ?string $method = null, ?Client $iClient = null, array &$bindings = []): Query
    {// 2023-05-10
        $iSharQ = new SharQ($this->iClient, $this->iSharQ->getSchema());

        $callback($iSharQ);

        $iSharQCompiler = new SharQCompiler($iClient ?? $this->iClient, $iSharQ, []);

        $iQuery = $iSharQCompiler->toQuery($method);

        $bindings = array_merge($bindings, $iQuery->getBindings());

        if ($iSharQ->getSingle()->alias !== null)
        {
            $iQuery->as($iSharQ->getSingle()->alias);
        }

        return $iQuery;
    }

    /**
     * @param array<int, string|Raw|SharQ>|null $tables
     * @return string
     */
    public function tables(?array $tables = null): string
    {// 2023-06-06
        $tables = $tables ?? $this->iSingle->table;

        if (!is_array($tables))
        {
            $tables = [$tables];
        }

        $tables = Utilities::arrayRemoveFalsey($tables);

        if (count($tables) === 0)
        {
            return '';
        }

        foreach ($tables as &$table)
        {
            if ($this->iSingle->schema !== null)
            {
                $table = $this->iSingle->schema.'.'.$table;
            }
        }

        $query = $this->columnize($tables);

        return $query;
    }

    /**
     * @param array<int, string|Raw|SharQ>|null $tables
     * @return string
     */
    public function compileFrom($tables): string
    {// 2023-05-15
        $query = $this->tables($tables);

        if ($query === '')
        {
            return '';
        }

        return " FROM {$query}";
    }

    /**
     * Compile hint comments
     * @return string
     */
    private function hintComments(): string
    {// 2023-05-15
        $iHintComments = $this->iStatementsGroupedOnType['HintComments'] ?? [];
        $hints         = array_map(fn($iHintComment) => implode(' ', $iHintComment->getComments()), $iHintComments);

        $hints = Utilities::arrayRemoveFalsey($hints);

        $hints = implode(' ', $hints);

        if ($hints === '')
        {
            return '';
        }

        return " /*+ {$hints} */";
    }

    /**
     * Compile comments
     * @return string
     */
    private function comments(): string
    {// 2023-06-07
        $iComments = $this->iStatementsGroupedOnType['Comments'] ?? [];

        $comments = [];

        foreach ($iComments as $iComment)
        {
            foreach (Utilities::arrayRemoveFalsey($iComment->getComments()) as $comment)
            {
                $comments[] = '/* '.str_replace(['/*', '*/'], ['', ''], $comment).' */';
            }
        }

        return implode(' ', $comments);
    }

    /**
     * Compiles a basic where clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereBasic(Where $iWhere): string
    {// 2023-05-16
        if ($iWhere->getType() !== Where::TYPE_BASIC)
        {
            throw new \Exception('Invalid where type');
        }

        $notFunction = $iWhere->isNot()
            ? 'NOT '
            : '';

        $column   = $this->wrap($iWhere->getColumn());
        $operator = $this->operator($iWhere->getOperator() ?? '=');
        $value    = $this->parameter($iWhere->getValue(), $this->bindings, false);

        if ($iWhere->getValue() instanceof \Closure)
        {
            $value = "({$value})";
        }

        $sql = "{$notFunction}{$column} {$operator} {$value}";

        return $sql;
    }

    /**
     * Compiles a raw where clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereRaw(Where $iWhere): string
    {// 2023-05-16
        if ($iWhere->getType() !== Where::TYPE_RAW)
        {
            throw new \Exception('Invalid where type');
        }

        $sql = $this->unwrapRaw($iWhere->getValue(), $this->bindings);

        $isNot = $iWhere->isNot()
            ? 'NOT '
            : '';

        return "{$isNot}{$sql}";
    }
    
    /**
     * Compiles a wrapped where clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereWrapped(Where $iWhere): string
    {// 2023-05-16
        if ($iWhere->getType() !== Where::TYPE_WRAPPED)
        {
            throw new \Exception('Invalid where type');
        }

        $notFunction = $iWhere->isNot()
            ? 'NOT '
            : '';

        $callback = $iWhere->getValue();

        $iQuery = $this->compileCallback($callback, 'where', $this->iClient, $this->bindings);

        $sql = $iQuery->toString(true, $this);

        $sql = substr($sql, 6);

        if ($sql)
        {
            return "{$notFunction}({$sql})";
        }

        return '';
    }
    
    /**
     * Compiles a where exists clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereExists(Where $iWhere): string
    {// 2023-05-16
        if ($iWhere->getType() !== Where::TYPE_EXISTS)
        {
            throw new \Exception('Invalid where type: '.$iWhere->getType());
        }

        $existsFunction = $iWhere->isNot()
            ? 'NOT EXISTS'
            : 'EXISTS';

        $value = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$existsFunction}({$value})";
    }
    
    /**
     * Compiles a where in clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereIn(Where $iWhere): string
    {// 2023-05-16
        if ($iWhere->getType() !== Where::TYPE_IN)
        {
            throw new \Exception('Invalid where type');
        }

        $column = $iWhere->getColumn();
        $values = $iWhere->getValue();

        $inFunction = $iWhere->isNot()
            ? 'NOT IN'
            : 'IN';

        // $valuesClause = $this->parametize($values, $this->bindings);
        $valuesClause = $this->values($values, $this->bindings);
        $columnClause = is_array($column) ? "({$this->columnize($column)})" : $this->wrap($column);

        return "{$columnClause} {$inFunction}{$valuesClause}";
    }
    
    /**
     * Compiles a where null clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereNull(Where $iWhere): string
    {// 2023-05-16
        $isFunction = $iWhere->isNot()
            ? 'IS NOT'
            : 'IS';

        $column = $this->wrap($iWhere->getColumn());

        return "{$column} {$isFunction} NULL";
    }
    
    /**
     * Compiles a where between clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereBetween(Where $iWhere): string
    {// 2023-05-16
        $betweenFunction = $iWhere->isNot()
            ? 'NOT BETWEEN'
            : 'BETWEEN';

        $column = $this->wrap($iWhere->getColumn());
        $values = array_map(fn($value) => $this->parameter($value, $this->bindings), $iWhere->getValue());
        $values = implode(' AND ', $values);

        return "{$column} {$betweenFunction} {$values}";
    }

    /**
     * Compiles a where like case-sensitive clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereLike(Where $iWhere): string
    {// 2023-05-16
        $column = $this->wrap($iWhere->getColumn());
        $value  = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$column} LIKE {$value} COLLATE utf8_bin";
    }

    /**
     * Compiles a where like incase-sensitive clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereILike(Where $iWhere): string
    {// 2023-05-16
        $column = $this->wrap($iWhere->getColumn());
        $value  = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$column} LIKE {$value}";
    }

    /**
     * Compiles a where column name clause.
     * @param Where $iWhere
     * @return string
     */
    private function whereColumn(Where $iWhere): string
    {// 2023-06-02
        if ($iWhere->getType() !== Where::TYPE_COLUMN)
        {
            throw new \Exception('Invalid where type: '.$iWhere->getType());
        }

        $column   = $this->wrap($iWhere->getColumn());
        $operator = $this->operator($iWhere->getOperator() ?? '=');
        $value    = $this->wrap($iWhere->getValue());

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    /**
     * Compiles the having clauses
     * @return string
     */
    public function having(): string
    {// 2023-06-05
        /** @var Having[] $iHavings */
        $iHavings = $this->iStatementsGroupedOnType['Having'] ?? [];

        $boolMap =
        [
            Where::BOOL_TYPE_AND => 'AND',
            Where::BOOL_TYPE_OR  => 'OR',
        ];

        $sql = [];

        foreach ($iHavings as $iHaving)
        {
            $value = null;

            if ($iHaving->getType() === Having::TYPE_BASIC)
            {
                $value = $this->havingBasic($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_WRAPPED)
            {
                $value = $this->havingWrapped($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_NULL)
            {
                $value = $this->havingNull($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_EXISTS)
            {
                $value = $this->havingExists($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_BETWEEN)
            {
                $value = $this->havingBetween($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_IN)
            {
                $value = $this->havingIn($iHaving);
            }
            else if ($iHaving->getType() === Having::TYPE_RAW)
            {
                $value = $this->havingRaw($iHaving);
            }

            if ($value === null)
            {
                throw new \Exception('Invalid having type "'.$iHaving->getType().'"');
            }

            $glue = (count($sql) === 0)
                ? 'HAVING'
                : $boolMap[$iHaving->getBoolType()];

            if (!$value)
            {
                continue;
            }
            
            $sql[] = $glue;
            $sql[] = $value;
        }

        $result = implode(' ', $sql);

        return $result;
    }

    /**
     * Compiles a basic having clause
     * @param Having $iHaving
     * @return string
     */
    private function havingBasic(Having $iHaving): string
    {// 2023-06-05
        $column   = $this->wrap($iHaving->getColumn());
        $operator = $this->operator($iHaving->getOperator() ?? '=');
        $value    = $this->parameter($iHaving->getValue(), $this->bindings);

        return "{$column} {$operator} {$value}";
    }

    /**
     * Compiles a wrapped having clause
     * @param Having $iHaving
     * @return string
     */
    private function havingWrapped(Having $iHaving): string
    {// 2023-06-05
        if ($iHaving->getType() !== Having::TYPE_WRAPPED)
        {
            throw new \Exception('Invalid having type');
        }

        $notFunction = $iHaving->isNot()
            ? 'NOT '
            : '';

        $callback = $iHaving->getValue();

        $iQuery = $this->compileCallback($callback, 'where', $this->iClient, $this->bindings);

        $sql = $iQuery->toString(true, $this);

        $sql = substr($sql, 6);

        if ($sql)
        {
            return "{$notFunction}({$sql})";
        }

        return '';
    }

    /**
     * Compiles a having null clause
     * @param Having $iHaving
     * @return string
     */
    private function havingNull(Having $iHaving): string
    {// 2023-06-05
        $column     = $this->wrap($iHaving->getColumn());
        $isFunction = $iHaving->isNot()
            ? 'IS NOT'
            : 'IS';

        return "{$column} {$isFunction} NULL";
    }

    /**
     * Compiles a having exists clause
     * @param Having $iHaving
     * @return string
     */
    private function havingExists(Having $iHaving): string
    {// 2023-06-05
        $column     = $this->wrap($iHaving->getColumn());
        $isFunction = $iHaving->isNot()
            ? 'NOT EXISTS'
            : 'EXISTS';

        return "{$isFunction} {$column}";
    }

    /**
     * Compiles a having between clause
     * @param Having $iHaving
     * @return string
     */
    private function havingBetween(Having $iHaving): string
    {// 2023-06-05
        $column          = $this->wrap($iHaving->getColumn());
        $betweenFunction = $iHaving->isNot()
            ? 'NOT BETWEEN'
            : 'BETWEEN';

        $values = array_map(fn($value) => $this->parameter($value, $this->bindings), $iHaving->getValue());
        $values = implode(' AND ', $values);

        return "{$column} {$betweenFunction} {$values}";
    }

    /**
     * Compiles a having in clause
     * @param Having $iHaving
     * @return string
     */
    private function havingIn(Having $iHaving): string
    {// 2023-06-05
        $column     = $this->wrap($iHaving->getColumn());
        $inFunction = $iHaving->isNot()
            ? 'NOT IN'
            : 'IN';

        $values = array_map(fn($value) => $this->parameter($value, $this->bindings), $iHaving->getValue());
        $values = implode(', ', $values);

        return "{$column} {$inFunction} ({$values})";
    }

    /**
     * Compiles a raw having clause
     * @param Having $iHaving
     * @return string
     */
    private function havingRaw(Having $iHaving): string
    {// 2023-06-05
        $value = $this->unwrapRaw($iHaving->getValue(), $this->bindings);

        return $value;
    }

    /**
     * Wraps a value in quotes appropriately. Queries and callbacks are wrapped.
     * @param int|float|string|Raw|SharQ|\Closure $value
     * @return string
     */
    public function wrap($value): string
    {// 2023-05-15
        if ($value instanceof Raw)
        {
            return $this->unwrapRaw($value, $this->bindings);
        }

        if ($value instanceof SharQ)
        {
            $sql = '('.$this->unwrapRaw($value, $this->bindings).')';

            if ($value->hasAlias())
            {
                $sql .= ' AS '.$this->wrap($value->getAlias());
            }

            return $sql;
        }

        if ($value instanceof \Closure)
        {
            return $this->compileCallback($value, null, null, $this->bindings)->toString(true, $this);
        }

        return $this->wrapIdentifier($value.'');
    }

    /**
     * @param string|Raw|SharQ $value
     * @return string
     */
    private function operator($value): string
    {// 2023-05-15
        if ($value instanceof Raw || $value instanceof SharQ)
        {
            return $this->unwrapRaw($value, $this->bindings);
        }

        $operator = self::QUERY_OPERATORS[trim(strtolower($value ?? ''))] ?? null;

        if ($operator === null)
        {
            throw new \InvalidArgumentException("The operator \"$value\" is not permitted");
        }

        return strtoupper($operator);
    }

    /**
     * Compiles columns clauses
     * @return string
     */
    public function columns(): ?string
    {// 2023-05-15
        if ($this->hasOnlyUnions())
        {
            return null;
        }

        if ($this->iSharQ->getMethod() === SharQ::METHOD_UPDATE)
        {
            return $this->sets();
        }

        if ($this->iSharQ->getMethod() === SharQ::METHOD_INSERT)
        {
            return $this->_insertBody($this->iSingle->insert);
        }
        
        $hints          = $this->hintComments();
        $isDistinct     = false;
        $distinctClause = null;

        $sql = [];
        /** @var Columns[] $iColumns */
        $iColumns = $this->iStatementsGroupedOnType['Columns'] ?? [];

        foreach ($iColumns as $iStatement)
        {
            $isDistinct = $isDistinct || $iStatement->isDistinct();

            if ($isDistinct && !$iStatement->isDistinct())
            {
                $distinctClause = 'DISTINCT ';
            }

            if ($iStatement->getType() === Columns::TYPE_PLUCK)
            {
                // $sql = array_merge($sql, $this->pluck($iStatement));
                $sql = array_merge($sql, $this->pluck($iStatement));
            }
            else if ($iStatement->getType() === Columns::TYPE_AGGREGATE)
            {
                $sql = array_merge($sql, $this->aggregate($iStatement));
            }
            else if ($iStatement->getType() === Columns::TYPE_AGGREGATE_RAW)
            {
                $sql[] = $this->aggregateRaw($iStatement);
            }
            // else if($iStatement->getType() === Columns::TYPE_ANALYTIC)
            // {
            //     $sql = array_merge($sql, $this->analytic($iStatement));
            // }
            else
            {
                throw new \Exception('Unknown column type');
            }
        }

        $sql = Utilities::arrayRemoveFalsey($sql);

        if (count($sql) === 0)
        {
            $sql = ['*'];
        } // Defaulting to selecting everything

        $selectSql = implode(', ', $sql);

        $query = "{$this->getMethodFunction()}{$hints} {$distinctClause}{$selectSql}{$this->compileFrom($this->iSingle->table)}";

        return $query;
    }

    private function getMethodFunction(?string $method = null)
    {
        $method = $method ?? $this->iSharQ->getMethod();

        $methodMap =
        [
            SharQ::METHOD_RAW      => '',
            SharQ::METHOD_SELECT   => 'SELECT',
            SharQ::METHOD_FIRST    => 'SELECT',
            SharQ::METHOD_PLUCK    => 'SELECT',
            SharQ::METHOD_INSERT   => 'INSERT',
            SharQ::METHOD_UPDATE   => 'UPDATE',
            SharQ::METHOD_DELETE   => 'DELETE',
            SharQ::METHOD_TRUNCATE => 'TRUNCATE',
        ];

        return $methodMap[$method];
    }

    /**
     * Creates alist of appropriately wrapped columns or queries, which can be used in ex. a select statement
     * @param array<int|string, string|Raw|SharQ|\Closure> $columns
     * @return string
     */
    public function columnize(array $columns): string
    {// 2023-05-31
        $query = '';

        if (count($columns) === 0)
        {
            return $query;
        }

        if (count($columns) === 1 && is_array(reset($columns)))
        {
            $columns = reset($columns);
        }
        
        $count = 0;

        foreach ($columns as $i => $column)
        {
            if ($count++ > 0)
            {
                $query .= ', ';
            }

            // Has alias
            if (is_string($i))
            {
                $alias = $i;

                if (is_array($column))
                {
                    $columnStr = $this->columnize($column);
                }
                else if ($column instanceof SharQ)
                {
                    $columnStr = "{$this->wrap($column)}";
                }
                else
                {
                    $columnStr = $this->wrap($column);
                }

                $query .= "{$columnStr} AS {$this->wrap($alias)}";

                continue;
            }

            if (is_array($column))
            {
                $query .= $this->columnize($column);

                continue;
            }

            $query .= $this->wrap($column);

            // if($column instanceof SharQ && $column->hasAlias()) $query .= " AS {$this->wrap($column->getAlias())}";
        }

        return $query;
    }

    /**
     * Creates a pluck clause
     * @param Columns $iColumns
     * @return array<int, string>
     */
    public function pluck(Columns $iColumns): array
    {// 2023-05-15
        $sql = $this->columnize($iColumns->getColumns());

        if ($iColumns->hasAlias())
        {
            $sql .= " AS {$this->wrap($iColumns->getAlias())}";
        }

        return [$sql];
    }

    /**
     * Creates a aggregate clause
     * @param Columns $iColumns
     * @return array<int, string>
     */
    public function aggregate(Columns $iColumns): array
    {// 2023-05-15
        $isDistinct     = $iColumns->isDistinct();
        $aliasSeperator = 'AS';

        $addAlias = function(string $value, ?string $alias = null) use ($aliasSeperator)
        {
            if ($alias === null)
            {
                return $value;
            }

            return "{$value} {$aliasSeperator} {$this->wrap($alias)}";
        };

        $aggregateArray = function(array $columns, ?string $alias = null) use ($isDistinct, $iColumns, $addAlias)
        {
            $columns    = array_map(fn($column) => $this->wrap($column), $columns);
            $columnsStr = implode(', ', $columns);

            if ($isDistinct)
            {
                $columnsStr = "DISTINCT {$columnsStr}";
            }

            $aggregate = "{$iColumns->getAggregateFunction()}({$columnsStr})";

            return $addAlias($aggregate, $alias);
        };

        /**
         * @param string|Raw $value
         * @param ?string $alias
         */
        $aggregateString = function($value, ?string $alias = null) use ($isDistinct, $iColumns, $addAlias)
        {// 2023-05-31
            $columnsStr = $this->wrap($value);

            if ($isDistinct)
            {
                $columnsStr = "DISTINCT {$columnsStr}";
            }

            $aggregate = "{$iColumns->getAggregateFunction()}({$columnsStr})";

            return $addAlias($aggregate, $alias);
        };

        $value = $iColumns->getColumns();

        if (count($value) === 1 && is_integer(key($value)))
        {
            $value = $value[0];
        }
        
        if (is_array($value))
        {
            if (is_integer(key($value)))
            {
                return [$aggregateArray($value, $iColumns->getAlias())];
            }

            // Is object
            if ($iColumns->hasAlias())
            {
                throw new \Exception('When using an object explicit alias can not be used');
            }


            return array_map(function($value, $alias) use ($aggregateArray, $aggregateString)
            {
                // if(is_integer($alias)) $alias = null;

                if (is_array($value))
                {
                    return $aggregateArray($value, $alias);
                }

                return $aggregateString($value, $alias);
            }, array_values($value), array_keys($value));
        }

        return [$aggregateString($value, $iColumns->getAlias())];
    }

    /**
     * Creates a aggregate raw clause
     * @param Columns $iColumns
     * @param array<string, mixed> $bindings
     * @return string
     */
    public function aggregateRaw(Columns $iColumns, array &$bindings = []): string
    {// 2023-05-15
        $rawStatements = [];

        foreach ($iColumns->getColumns() as $iRaw)
        {
            $rawStatements[] = $this->unwrapRaw($iRaw, $bindings);
        }

        $distinctClause = $iColumns->isDistinct() ? 'DISTINCT ' : '';
        $statement      = implode(', ', $rawStatements);

        $query = "{$iColumns->getAggregateFunction()}({$distinctClause}{$statement})";

        if ($iColumns->hasAlias())
        {
            $query .= " AS {$this->wrap($iColumns->getAlias())}";
        }

        return $query;
    }

    /**
     * Compiles all statements into a full query
     * @return string
     */
    public function compileStatements(string $method): string
    {// 2023-06-06
        // With always comes first
        // $query = $this->with();

        $query = '';

        $componentsMap =
        [
            'SELECT'   => self::QUERY_COMPONENTS,
            'INSERT'   => self::INSERT_COMPONENTS,
            'UPDATE'   => self::UPDATE_COMPONENTS,
            'DELETE'   => self::DELETE_COMPONENTS,
            'TRUNCATE' => self::TRUNCATE_COMPONENTS,
            'RAW'      => self::RAW_COMPONENTS,
        ];

        $components = $componentsMap[$this->iSharQ->getMethod()] ?? self::QUERY_COMPONENTS;

        $index      = array_search($method, $components);
        $components = array_slice($components, (int)$index);
        
        $unionStatement  = null;
        $firstStatements = [];
        $endStatements   = [];

        foreach ($components as $component)
        {
            $statement = $this->{$component}($this);

            switch($component)
            {
                case 'union':
                    $unionStatement = $statement;
                    break;
                case 'comments':
                case 'with':
                case 'method':
                case 'columns':
                case 'join':
                case 'where':
                    $firstStatements[] = $statement;
                    break;
                default:
                    $endStatements[] = $statement;
                    break;
            }
        }

        // Check if we need to wrap the main query.
        // We need to wrap main query if one of union have wrap options to true
        // to avoid error syntax (in PostgreSQL for example).
        $wrapMainQuery = array_reduce($this->iStatementsGroupedOnType['Union'] ?? [], fn($carry, $next) => $carry || $next->isWrapping(), false);

        $firstStatements = Utilities::arrayRemoveFalsey($firstStatements);
        $endStatements   = Utilities::arrayRemoveFalsey($endStatements);

        if ($this->hasOnlyUnions())
        {
            $query .= implode(' ', array_merge(
                $firstStatements,
                $endStatements));

            return $query === ''
                ? $unionStatement
                : "{$unionStatement} {$query}";
        }
        else
        {
            $allStatements = $wrapMainQuery
                ? '('.implode(' ', $firstStatements).')'
                : implode(' ', $firstStatements);

            $endStats = implode(' ', $endStatements);

            if ($endStats)
            {
                $endStats = ' '.$endStats;
            }

            $query .=
                $allStatements.
                ($unionStatement ? ' '.$unionStatement : '').
                $endStats;
        }

        // if($this->iSingle->alias !== null) $query = "({$query}) AS {$this->wrap($this->iSingle->alias)}";

        return $query;
    }

    /**
     * Compiles the appropriate method clause
     * @return string
     */
    public function method(): string
    {// 2023-06-06
        $methodMap =
        [
            'RAW'      => 'raw',
            'SELECT'   => 'select',
            'FIRST'    => 'select',
            'INSERT'   => 'insert',
            'UPDATE'   => 'update',
            'DELETE'   => 'delete',
            'TRUNCATE' => 'truncate',
        ];

        return call_user_func([$this, $methodMap[$this->iSharQ->getMethod()]]);
    }

    public function raw(): string
    {// 2023-06-06
        $iRaws = $this->iStatementsGroupedOnType['Raw'] ?? [];

        $iRaw = $iRaws[0] ?? null; // There can only be one raw statement

        return $this->unwrapRaw($iRaw, $this->bindings);
    }

    /**
     * Compiles the appropriate select clause
     * @return string
     */
    public function select(): string
    {// 2023-05-10
        // With always comes first
        // $query = $this->with();

        return $this->columns();
    }

    /**
     * Normalizes the update statements into a single array. compiles statements.
     * @return array<int,string>
     */
    private function _prepUpdate(array $data = []): array
    {// 2023-06-06
        $counter = $this->iSingle->counter ?? [];

        foreach ($counter as $column => $amount)
        {
            if (isset($data[$column]))
            {
                throw new \Exception("Column {$column} is already set in update");
            }

            $symbol = $amount > 0 ? '+' : '-';

            if ($amount < 0)
            {
                $amount = abs($amount);
            }

            $data[$column] = new Raw("?? {$symbol} ?", $column, $amount);
        }

        $values = [];

        foreach ($data as $column => $value)
        {
            $values[] = "{$this->wrap($column)} = {$this->parameter($value, $this->bindings)}";
        }

        if (count($values) === 0)
        {
            $error =
            [
                'Empty .update() call detected!',
                'Update data does not contain any values to update.',
                'This will result in a faulty query.',
                print_r($this->iSingle->table, true).'.',
                'Columns: '.print_r(array_keys($this->iSingle->update), true).'.'
            ];

            throw new \Exception(implode(' ', $error));
        }

        return $values;
    }

    /**
     * Compiles the appropriate update clause
     * @return string
     */
    public function sets(): string
    {// 2023-06-06
        $updateData = $this->_prepUpdate($this->iSingle->update ?? []);

        $sql = implode(', ', $updateData);

        if ($sql === '')
        {
            return '';
        }

        return "SET {$sql}";
    }

    /**
     * Compiles the update method
     * @return string
     */
    public function update(): string
    {// 2023-06-05
        $tableName = $this->tables();

        return "UPDATE {$tableName}";
    }

    /**
     * Compiles the update method
     * @return string
     */
    public function insert(): string
    {// 2023-06-06
        $tableName = $this->tables();

        $ignore = $this->iSingle->ignore ? ' IGNORE' : '';

        return "INSERT{$ignore} INTO {$tableName}";
    }

    /**
     * @param array<int, string|Raw|SharQ> $values
     * @return string
     */
    private function _buildInsertValues($values): string
    {// 2023-06-06
        $sql   = '';
        $count = 0;

        foreach ($values as $value)
        {
            if ($count++ !== 0)
            {
                $sql .= '), (';
            }

            $sql .= $this->parametize($value, $this->bindings);
        }

        return $sql;
    }
    /**
     * @param array<int, string|Raw|SharQ> $insertValues
     * @return string
     */
    private function _insertBody($insertValues): string
    {// 2023-06-06
        if ($insertValues instanceof SharQ)
        {
            return $this->unwrapRaw($insertValues, $this->bindings);
        }

        if ($insertValues instanceof \Closure)
        {
            return $this->compileCallback($insertValues, null, null, $this->bindings)->toString(false, $this);
        }

        if (is_array($insertValues) && count($insertValues) === 0)
        {
            return '';
        }

        $insertData = $this->_prepInsert($insertValues);

        if (is_string($insertData))
        {
            return $insertData;
        }

        $columns = $insertData['columns'];
        $values  = $insertData['values'];

        $sql = '';

        if (count($columns) !== 0)
        {
            $sql .= '('.$this->columnize($columns);

            $sql .= ') VALUES (';

            $sql .= $this->_buildInsertValues($values).')';
        }

        if ($this->iSingle->merge !== null)
        {
            $sql .= $this->_merge($this->iSingle->merge, $this->iSingle->insert);
        }

        return $sql;
    }

    /**
     * @param array<int, string|Raw|SharQ> $data
     * @return array<string,array<int, string>>
     */
    private function _prepInsert($data): array
    {// 2023-06-06
        if ($data instanceof Raw)
        {
            return $this->unwrapRaw($data, $this->bindings);
        }

        if (!is_array($data))
        {
            $data = [$data];
        }

        if (!is_array(reset($data)))
        {
            $data = [$data];
        }

        // If we some rows are missing data, then we will have to fill in with DEFAULT values
        // to ensure that the insert will have the correct column count.
        $columns = array_unique(array_reduce(array_map('array_keys', $data), 'array_merge', []));
        sort($columns);

        $values = [];

        foreach ($data as $dataRow)
        {
            if (count($dataRow) === count($columns))
            {
                ksort($dataRow);
                $values[] = $dataRow;
                continue;
            }

            $missingColumns = array_diff($columns, array_keys($dataRow));

            foreach ($missingColumns as $missingColumn)
            {
                $dataRow[$missingColumn] = new Raw('DEFAULT');
            }

            ksort($dataRow);
            $values[] = $dataRow;
        }

        return ['columns' => $columns, 'values' => $values];
    }

    /**
     * @param array<int, string|Raw|SharQ> $updates
     * @param array<int, string|Raw|SharQ> $insert
     * @return string
     */
    private function _merge($updates, $insert): string
    {// 2023-06-07
        $sql = ' ON DUPLICATE KEY UPDATE ';

        if (is_array($updates) && is_integer(key($updates)))
        {
            $sql .= implode(', ', array_map(fn($v) => "{$v} = VALUES({$v})", array_map(fn($v) => $this->wrap($v), $updates)));
        }
        else if (is_array($updates) && is_string(key($updates)))
        {
            $updateData = $this->_prepUpdate($updates);
            
            $sql .= implode(', ', $updateData);
        }
        else if (is_string($updates))
        {
            $sql .= $updates;
        }
        else
        {
            $insertData = $this->_prepInsert($insert);

            if (is_string($insertData))
            {
                throw new \Exception('If using merge with a raw insert query, then updates must be provided');
            }

            $sql .= implode(', ', array_map(fn($v) => "{$v} = VALUES({$v})", array_map(fn($v) => $this->wrap($v), $insertData['columns'])));
        }

        return $sql;
    }

    /**
     * Compiles with clause
     * @return array<int, string>
     */
    public function with(): string
    {// 2023-05-10
        /** @var With[] $iWithStatements */
        $iWithStatements = $this->iStatementsGroupedOnType['With'] ?? [];

        if (count($iWithStatements) === 0)
        {
            return '';
        }

        $sqlStrings   = [];
        $hasRecursive = false;

        foreach ($iWithStatements as $iWithStatement)
        {// 2023-05-10
            $hasRecursive = $hasRecursive || $iWithStatement->isRecursive();

            $sql = null;

            if ($iWithStatement->getType() === With::TYPE_WRAPPED)
            {
                $sql = $this->withWrapped($iWithStatement);
            }
            else if ($iWithStatement->getType() === With::TYPE_RECURSIVE_WRAPPED)
            {
                $sql = $this->withRecursiveWrapped($iWithStatement);
            }
            else if ($iWithStatement->getType() === With::TYPE_MATERIALIZED_WRAPPED)
            {
                $sql = $this->withMaterializedWrapped($iWithStatement);
            }
            else if ($iWithStatement->getType() === With::TYPE_NOT_MATERIALIZED_WRAPPED)
            {
                $sql = $this->withNotMaterializedWrapped($iWithStatement);
            }
            else
            {
                throw new \Exception('Unknown with type: '.$iWithStatement->getType());
            }

            $sqlStrings[] = $sql;
        }
        
        return "WITH ".($hasRecursive ? 'RECURSIVE ' : '').implode(', ', $sqlStrings);
    }

    private function withWrapped(With $iWithStatement): string
    {// 2023-07-31
        // $value = $this->parameter($iWithStatement->getValue(), $this->bindings, true);
        // $value = $this->values($iWithStatement->getValue(), $this->bindings);
        $value   = $this->wrap($iWithStatement->getValue());
        $columns = implode(',', array_map(fn($v) => $this->wrap($v), $iWithStatement->getColumns() ?? []));

        if ($columns !== '')
        {
            $columns = " ($columns)";
        }

        $alias = $this->wrap($iWithStatement->getAlias());

        $sql = "$alias$columns AS $value";

        return $sql;
    }

    private function withRecursiveWrapped(With $iWithStatement): string
    {// 2023-07-31
        return $this->withWrapped($iWithStatement);
    }

    private function withMaterializedWrapped()
    {
    }

    private function withNotMaterializedWrapped()
    {
    }

    /**
     * Compiles truncate method
     * @return string
     */
    public function truncate(): string
    {// 2023-06-06
        $tableName = $this->tables();

        return "TRUNCATE {$tableName}";
    }

    /**
     * Compiles delete method
     * @return string
     */
    public function delete(): string
    {// 2023-06-06
        if ($this->iSingle->delete !== null)
        {
            if (count($this->iSingle->delete) > 1 && $this->iSingle->table === null)
            {
                throw new \InvalidArgumentException('When deleting from multiple tables, a table must be provided');
            }
        }

        $fromName     = $this->tables();
        $deleteTables = $this->tables($this->iSingle->delete ?? null);

        return "DELETE {$deleteTables} FROM {$fromName}";
    }

    /**
     * Compiles update method
     * @param Clause $iClause
     * @return string
     */
    private function processJoinClause(Clause $iClause): ?string
    {// 2023-06-01
        $value = null;

        if ($iClause->getType() === Join::ON_TYPE_RAW)
        {
            $value = $this->onRaw($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_BASIC)
        {
            $value = $this->onBasic($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_VALUE)
        {
            $value = $this->onValue($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_BETWEEN)
        {
            $value = $this->onBetween($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_WRAPPED)
        {
            $value = $this->onWrapped($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_USING)
        {
            $value = $this->onUsing($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_IN)
        {
            $value = $this->onIn($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_NULL)
        {
            $value = $this->onNull($iClause, $this->bindings);
        }
        else if ($iClause->getType() === Join::ON_TYPE_EXISTS)
        {
            $value = $this->onExists($iClause, $this->bindings);
        }
            
        return $value;
    }

    /**
     * Compiles join statements
     * @return string
     */
    public function join(): string
    {// 2023-05-15
        $sql = '';

        /** @var Join[] $iJoins */
        $iJoins = $this->iStatementsGroupedOnType['Join'] ?? [];

        $count = 0;

        foreach ($iJoins as $iJoin)
        {
            if ($count++ > 0)
            {
                $sql .= ' ';
            }

            $tableName = $iJoin->getTableName();

            if ($this->iSingle->schema !== null && !($tableName instanceof Raw))
            {
                $tableName = $this->iSingle->schema.'.'.$tableName;
            }

            $sql .= ($iJoin->getType() === Join::TYPE_RAW)
                ? $this->unwrapRaw($iJoin->getTableName(), $this->bindings)
                : $iJoin->getJoinFunction().' '.$this->wrap($tableName);

            if ($iJoin->getAlias() !== null)
            {
                $sql .= ' AS '.$this->wrap($iJoin->getAlias());
            }

            $clausesCount = 0;

            foreach ($iJoin->getClauses() as $iClause)
            {
                $sql .= ($clausesCount++ > 0)
                    ? " {$iClause->getBoolFunction()} "
                    : " {$iClause->getOnFunction()}(";

                $value = $this->processJoinClause($iClause);

                if ($value)
                {
                    $sql .= $value;
                }
            }

            if ($clausesCount !== 0)
            {
                $sql .= ')';
            }
        }

        return $sql;
    }

    /**
     * Compiles on raw clause
     * @param Clause $iClause
     * @return string
     */
    private function onRaw(Clause $iClause): string
    {// 2023-05-31
        return $this->unwrapRaw($iClause->getValue(), $this->bindings);
    }

    /**
     * Compiles basic on clause
     * @param Clause $iClause
     * @return string
     */
    private function onBasic(Clause $iClause): string
    {// 2023-05-31
        // $wrap = $iClause->getValue() instanceof SharQ;

        $column   = $this->wrap($iClause->getColumn());
        $operator = $this->operator($iClause->getOperator());

        $value = $this->wrap($iClause->getValue()); 

        // if($wrap) $value = "({$value})";

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    /**
     * Compiles on value clause
     * @param Clause $iClause
     * @return string
     */
    private function onValue(Clause $iClause): string
    {// 2023-05-31
        $column   = $this->wrap($iClause->getColumn());
        $operator = $this->operator($iClause->getOperator());
        $value    = $this->parameter($iClause->getValue(), $this->bindings);

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    /**
     * Compiles on between clause
     * @param Clause $iClause
     * @return string
     */
    private function onBetween(Clause $iClause): string
    {// 2023-06-01
        $betweenFunction = $iClause->isNot()
            ? 'NOT BETWEEN'
            : 'BETWEEN';

        $column = $this->wrap($iClause->getColumn());
        $values = array_map(function($value)
        { return $this->parameter($value, $this->bindings); }, $iClause->getValue());
        $values = implode(' AND ', $values);

        $sql = "{$column} {$betweenFunction} {$values}";

        return $sql;
    }

    /**
     * Compiles on wrapped clause
     * @param Clause $iClause
     * @return string
     */
    private function onWrapped(Clause $iClause): string
    {// 2023-05-31
        $iJoin    = new Join($iClause->getValue(), Join::TYPE_RAW);
        $callback = $iClause->getValue();

        $callback($iJoin);

        $sql   = '';
        $count = 0;

        foreach ($iJoin->getClauses() as $iClause)
        {
            if ($count++ > 0)
            {
                $sql .= " {$iClause->getBoolFunction()} ";
            }

            $val = $this->processJoinClause($iClause);

            if ($val)
            {
                $sql .= $val;
            }
        }

        if ($sql)
        {
            return "({$sql})";
        }

        return $sql;
    }

    /**
     * Compiles using clause
     * @param Clause $iClause
     * @return string
     */
    private function onUsing(Clause $iClause): string
    {// 2023-05-31
        return "{$this->columnize([$iClause->getColumn()])}";
    }

    /**
     * Compiles on in clause
     * @param Clause $iClause
     * @return string
     */
    private function onIn(Clause $iClause): string
    {// 2023-05-31
        if (is_array($iClause->getValue()) && is_array($iClause->getValue()[0] ?? 0))
        {
            return $this->onInMultiple($iClause);
        }

        $values = null;

        if ($iClause->getValue() instanceof Raw)
        {
            $values = $this->parameter($iClause->getValue(), $this->bindings);
        }
        else
        {
            $values = $this->parametize($iClause->getValue(), $this->bindings);
        }

        $onFunction = $iClause->isNot()
            ? 'NOT IN'
            : 'IN';

        return "{$this->wrap($iClause->getColumn())} {$onFunction}({$values})";
    }

    /**
     * Compiles on in multiple clause
     * @param Clause $iClause
     * @return string
     */
    private function onInMultiple(Clause $iClause): string
    {// 2023-05-31
        $sql = $this->columnize([$iClause->getColumn()]);

        $onFunction = $iClause->isNot()
            ? 'NOT IN'
            : 'IN';
        
        $sql .= "{$onFunction} ((";
        $count = 0;

        foreach ($iClause->getValue() as $value)
        {
            if ($count++ > 0)
            {
                $sql .= '),(';
            }

            $sql .= $this->parametize($value, $this->bindings);
        }

        return $sql.'))';
    }

    /**
     * Compiles on null clause
     * @param Clause $iClause
     * @return string
     */
    private function onNull(Clause $iClause): string
    {// 2023-05-31
        $column = $this->wrap($iClause->getColumn());

        $isFunction = $iClause->isNot()
            ? 'IS NOT'
            : 'IS';

        $value = 'NULL';

        return "{$column} {$isFunction} {$value}";
    }

    /**
     * Compiles on exists clause
     * @param Clause $iClause
     * @return string
     */
    private function onExists(Clause $iClause): string
    {// 2023-05-31
        $existsFunction = $iClause->isNot()
            ? 'NOT EXISTS'
            : 'EXISTS';

        return "{$existsFunction}({$this->parameter($iClause->getValue(), $this->bindings)})";
    }

    /**
     * Compiles where statements
     * @return string
     */
    public function where(): string
    {// 2023-05-15
        /** @var Where[] $iWheres */
        $iWheres = $this->iStatementsGroupedOnType['Where'] ?? [];

        $boolMap =
        [
            Where::BOOL_TYPE_AND => 'AND',
            Where::BOOL_TYPE_OR  => 'OR',
        ];

        $sql = [];

        foreach ($iWheres as $iWhere)
        {
            $value = null;

            if ($iWhere->getType() === Where::TYPE_BASIC)
            {
                $value = $this->whereBasic($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_RAW)
            {
                $value = $this->whereRaw($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_WRAPPED)
            {
                $value = $this->whereWrapped($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_EXISTS)
            {
                $value = $this->whereExists($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_IN)
            {
                $value = $this->whereIn($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_NULL)
            {
                $value = $this->whereNull($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_BETWEEN)
            {
                $value = $this->whereBetween($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_LIKE)
            {
                $value = $this->whereLike($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_ILIKE)
            {
                $value = $this->whereILike($iWhere);
            }
            else if ($iWhere->getType() === Where::TYPE_COLUMN)
            {
                $value = $this->whereColumn($iWhere);
            }

            if ($value === null)
            {
                throw new \Exception('Invalid where type "'.$iWhere->getType().'"');
            }

            $glue = (count($sql) === 0)
                ? 'WHERE'
                : $boolMap[$iWhere->getBoolType()];

            if (!$value)
            {
                continue;
            }
            
            $sql[] = $glue;
            $sql[] = $value;
        }

        return implode(' ', $sql);
    }

    /**
     * Compiles limit statement
     * @return string
     */
    public function limit(): string
    {// 2023-05-15
        $noLimit = $this->iSingle->limit === null;

        if ($noLimit)
        {
            return '';
        }

        $limit = $this->parameter($this->iSingle->limit, $this->bindings);

        return "LIMIT {$limit}";
    }

    /**
     * Compiles offset statement
     * @return string
     */
    public function offset()
    {// 2023-05-15
        $noOffset = $this->iSingle->offset === null;

        if ($noOffset)
        {
            return '';
        }

        $noLimit = $this->iSingle->limit === null;

        $limit = '';

        if ($noLimit)
        {
            $this->iSingle->limit = new Raw('18446744073709551615');
            $limit                = $this->limit().' ';
        }

        $offset = $this->parameter($this->iSingle->offset, $this->bindings);

        return "{$limit}OFFSET {$offset}";
    }

    /**
     * Compiles group by statement
     * @param string|Raw|SharQ $value
     * @param Order::TYPE_NULLS_POSITION_* $nullsPosition
     * @return string
     */
    private function _orderBy($value, $nullsPosition): string
    {// 2023-06-05
        $nullOrder = '';

        if ($nullsPosition === Order::TYPE_NULLS_POSITION_FIRST)
        {
            $nullOrder = ' IS NOT NULL';
        }
        else if ($nullsPosition === Order::TYPE_NULLS_POSITION_LAST)
        {
            $nullOrder = ' IS NULL';
        }

        if ($value instanceof Raw)
        {
            return $this->unwrapRaw($value, $this->bindings);
        }

        if (is_string($value))
        {
            $value = [$value];
        }

        if ($value instanceof SharQ || $nullsPosition !== null)
        {
            if ($value instanceof SharQ)
            {
                $value = [$value];
            }

            $orderBy = $this->columnize($value).$nullOrder;

            if ($nullsPosition !== null)
            {
                $orderBy = '('.$orderBy.')';
            }

            return $orderBy;
        }

        return $this->columnize($value);
    }

    /**
     * Compiles the `group by` statements.
     * @param string|Raw|SharQ $value
     * @return string
     */
    private function _groupBy($value): string
    {// 2023-06-05
        return $this->_orderBy($value, null);
    }

    /**
     * Compiles group by statements
     * @return string
     */
    public function group(): string
    {// 2023-06-05
        /** @var Group[] $iGroupBys */
        $iGroupBys = $this->iStatementsGroupedOnType['Group'] ?? [];

        if (count($iGroupBys) === 0)
        {
            return '';
        }

        $sql = [];

        foreach ($iGroupBys as $iGroupBy)
        {
            $sql[] = $this->_groupBy($iGroupBy->getColumn());
        }

        $result = 'GROUP BY '.implode(', ', $sql);

        return $result;
    }

    /**
     * Compiles order by statements
     * @return string
     */
    public function order(): string
    {// 2023-06-05
        /** @var Order[] $iOrderBys */
        $iOrderBys = $this->iStatementsGroupedOnType['Order'] ?? [];

        if (count($iOrderBys) === 0)
        {
            return '';
        }

        $directionMap =
        [
            'asc'  => Order::DIRECTION_ASC,
            'desc' => Order::DIRECTION_DESC,
        ];

        $nullOrderMap =
        [
            Order::TYPE_NULLS_POSITION_FIRST => Order::TYPE_NULLS_POSITION_FIRST,
            Order::TYPE_NULLS_POSITION_LAST  => Order::TYPE_NULLS_POSITION_LAST,

            'first' => Order::TYPE_NULLS_POSITION_FIRST,
            'last'  => Order::TYPE_NULLS_POSITION_LAST,
        ];

        $sql = [];

        foreach ($iOrderBys as $iOrderBy)
        {
            $direction = $directionMap[trim(strtolower($iOrderBy->getDirection() ?? ''))] ?? '';

            $direction = $direction !== ''
                ? ' '.$direction
                : '';

            $nullsPosition = $nullOrderMap[$iOrderBy->getNullsPosition()] ?? null;

            $sql[] = $this->_orderBy($iOrderBy->getColumn(), $nullsPosition).$direction;
        }

        return 'ORDER BY '.implode(', ', $sql);
    }

    /**
     * Compiles union statements
     * @return string
     */
    public function union(): string
    {// 2023-06-07
        /** @var Union[] $iUnions */
        $iUnions = $this->iStatementsGroupedOnType['Union'] ?? [];

        if (count($iUnions) === 0)
        {
            return '';
        }

        $sql = '';

        foreach ($iUnions as $i => $iUnion)
        {
            if ($i > 0)
            {
                $sql .= ' ';
            }

            if ($i > 0 || !$this->hasOnlyUnions())
            {
                $sql .= $iUnion->getClause().' ';
            }

            $statement = $this->parameter($iUnion->getStatement(), $this->bindings, false);
            // $statement = $this->columnize([$iUnion->getStatement()], $this->bindings);

            if ($statement)
            {
                $statement = $iUnion->isWrapping()
                    ? '('.$statement.')'
                    : $statement;

                $sql .= $statement;
            }
        }

        return $sql;
    }

    /**
     * Compiles lock statements
     * @return string
     * @throws \Exception
     */
    public function lock(): string
    {// 2023-05-15
        if ($this->iSingle->lock === null)
        {
            return '';
        }

        if ($this->iSingle->lock === SharQ::LOCK_MODE_FOR_UPDATE)
        {
            return $this->forUpdate();
        }

        if ($this->iSingle->lock === SharQ::LOCK_MODE_FOR_SHARE)
        {
            return $this->forShare();
        }

        if ($this->iSingle->lock === SharQ::LOCK_MODE_FOR_NO_KEY_UPDATE)
        {
            return $this->forNoKeyUpdate();
        }

        if ($this->iSingle->lock === SharQ::LOCK_MODE_FOR_KEY_SHARE)
        {
            return $this->forKeyShare();
        }

        throw new \Exception('Invalid lock mode "'.$this->iSingle->lock.'"');
    }

    /**
     * Compiles for update statement
     * @return string
     * @throws \Exception
     */
    private function forUpdate(): string
    {// 2023-05-15
        return 'FOR UPDATE';
    }

    /**
     * Compiles for share statement
     * @return string
     * @throws \Exception
     */
    private function forShare(): string
    {// 2023-05-15
        return 'LOCK IN SHARE MODE';
    }

    /**
     * Compiles for no key update statement
     * @return string
     * @throws \Exception
     */
    private function forNoKeyUpdate(): string
    {// 2023-05-15
        throw new \Exception('Not implemented');
    }

    /**
     * Compiles for key share statement
     * @return string
     * @throws \Exception
     */
    private function forKeyShare(): string
    {// 2023-05-15
        throw new \Exception('Not implemented');
    }

    /**
     * Compiles wait mode statement
     * @return string
     * @throws \Exception
     */
    public function waitMode(): string
    {// 2023-06-07
        if ($this->iSingle->waitMode === null)
        {
            return '';
        }

        if ($this->iSingle->waitMode === SharQ::WAIT_MODE_SKIP_LOCKED)
        {
            return $this->skipLocked();
        }

        if ($this->iSingle->waitMode === SharQ::WAIT_MODE_NO_WAIT)
        {
            return $this->noWait();
        }

        throw new \Exception('Invalid wait mode "'.$this->iSingle->waitMode.'"');
    }

    /**
     * Compiles skip locked statement
     * @return string
     * @throws \Exception
     */
    private function skipLocked(): string
    {// 2023-06-07
        return 'SKIP LOCKED';
    }

    /**
     * Compiles NOWAIT statement
     * @return string
     * @throws \Exception
     */
    private function noWait(): string
    {// 2023-06-07
        return 'NOWAIT';
    }

    /**
     * Checks whether a value is a function... if it is, we compile it otherwise we check whether it's a raw
     * @param $value
     * @param array<int, mixed> $bindings
     * @param bool $wrap
     * @return string
     */
    private function parameter($value, array &$bindings, bool $wrap = false): string
    {// 2023-05-31
        if ($value instanceof \Closure)
        {
            return $this->compileCallback($value, null, null, $bindings)->toString($wrap, $this);
        }

        if ($value instanceof SharQ)
        {
            return $this->unwrapRaw($value, $this->bindings);
        }

        return $this->unwrapRaw($value, $bindings) ?? '?';
    }

    /**
     * Take an array of values and runs parameter on them.
     *
     * @param array<int, mixed> $values
     * @param array<int, mixed> $bindings
     * @return string
     */
    private function parametize($values, array &$bindings): string
    {// 2023-05-31
        if ($values instanceof \Closure)
        {
            return $this->parameter($values, $bindings);
        }

        if (!is_array($values))
        {
            $values = [$values];
        }

        $sql   = '';
        $count = 0;

        foreach ($values as $value)
        {
            if ($count++ > 0)
            {
                $sql .= ', ';
            }

            $parameter = $this->parameter($value, $bindings);

            if ($value instanceof SharQ)
            {
                $parameter = '('.$parameter.')';

                if ($value->getAlias() !== null)
                {
                    $parameter .= ' AS '.$this->wrap($value->getAlias());
                }
            }

            $sql .= $parameter;
        }

        return $sql;
    }

    /**
     * Formats `values` into a parenthesized list of parameters for a `VALUES`
     * clause.
     *
     * [1, 2]                  -> '(?, ?)'
     * [[1, 2], [3, 4]]        -> '((?, ?), (?, ?))'
     * knex('table')		   -> '(select * from "table")'
     * knex.raw('select ?', 1) -> '(select ?)'
     *
     * @param array<int, mixed>|SharQ|Raw $values
     * @param array<int, mixed> $bindings
     * @return string
     */
    private function values($values, array &$bindings): string
    {// 2023-06-02
        if ($values instanceof Raw || $values instanceof SharQ)
        {
            return "({$this->parameter($values, $bindings)})";
        }

        if (is_array($values))
        {
            if (!is_array($values[0] ?? 0))
            {
                return "({$this->parametize($values, $bindings)})";
            }

            // $sqlValues = array_map(fn($value) => "({$this->parametize($value, $bindings)})", $values);
            $sqlValues = [];

            foreach ($values as $value)
            {
                $sqlValues[] = "({$this->parametize($value, $bindings)})";
            }
            $sqlValues = implode(', ', $sqlValues);

            return "({$sqlValues})";
        }

        return "({$this->parameter($values, $bindings)})";
    }
}
