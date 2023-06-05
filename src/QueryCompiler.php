<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

use Sharksmedia\QueryBuilder\Formatter\WrappingFormatter;
use Sharksmedia\QueryBuilder\Statement\Columns;
use Sharksmedia\QueryBuilder\Statement\Clause;
use Sharksmedia\QueryBuilder\Statement\Join;
use Sharksmedia\QueryBuilder\Statement\Where;
use Sharksmedia\QueryBuilder\Statement\Raw;
use Sharksmedia\QueryBuilder\Statement\With;
use Sharksmedia\QueryBuilder\Utilities;

class QueryCompiler
{
    private Client           $iClient;
    private QueryBuilder     $iQueryBuilder;
    private array            $bindings;
    private Single           $iSingle;

    private array $iStatementsGroupedOnType;

    private string $method;
    private array $options = [];
    private bool $single;
    private int $timeout = 0;
    private bool $cancelOnTimeout = false;
    private string $grouped;
    // private Formatter $iFormatter;

    public const QUERY_COMPONENTS =
    [
        // 'with',
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

    public const QUERY_OPERATORS =
    [
        '='=>'=',
        '<'=>'<',
        '>'=>'>',
        '<='=>'<=',
        '>='=>'>=',
        '<>'=>'<>',
        '!='=>'!=',
        'like'=>'like',
        'not like'=>'not like',
        'between'=>'between',
        'not between'=>'not between',
        'ilike'=>'ilike',
        'not ilike'=>'not ilike',
        'exists'=>'exists',
        'not exist'=>'not exist',
        'rlike'=>'rlike',
        'not rlike'=>'not rlike',
        'regexp'=>'regexp',
        'not regexp'=>'not regexp',
        'match'=>'match',
        '&'=>'&',
        '|'=>'|',
        '^'=>'^',
        '<<'=>'<<',
        '>>'=>'>>',
        '~'=>'~',
        '~='=>'~=',
        '~*'=>'~*',
        '!~'=>'!~',
        '!~*'=>'!~*',
        '#'=>'#',
        '&&'=>'&&',
        '@>'=>'@>',
        '<@'=>'<@',
        '||'=>'||',
        '&<'=>'&<',
        '&>'=>'&>',
        '-|-'=>'-|-',
        '@@'=>'@@',
        '!!'=>'!!',
    ];
    /**
     * @param array<int,mixed> $bindings
     */
    public function __construct(Client $iClient, QueryBuilder $iQueryBuilder, array $bindings)
    {// 2023-05-10
        $this->iClient = $iClient;
        $this->iQueryBuilder = $iQueryBuilder;
        $this->bindings = $bindings;

        $this->iSingle = $iQueryBuilder->getSingle();

        // $this->iFormatter = $iClient->getFormatter();

        $this->iStatementsGroupedOnType = array_reduce($iQueryBuilder->getStatements(), function($curr, $next)
        {
            $curr[$next->getClass()][] = $next;
            return $curr;
        }, []);
    }

    public function getBindings(): array
    {// 2023-05-16
        return $this->bindings;
    }

    public function toSQL(string $method): Query
    {// 2023-05-15
        $iQuery = new Query($method, $this->options, $this->timeout, $this->cancelOnTimeout, $this->bindings, '<UUID>');

        $value = $this->{$method}();

        $iQuery->setSQL($value);
        $iQuery->setBindings($this->bindings);

        return $iQuery;
    }
    /**
     * @param mixed $value
     */
    private function unwrapRaw($value, array &$bindings)
    {// 2023-05-10
        if($value instanceof Raw)
        {
            $sql = $value->getSQL();
            $binds = $value->getBindings();

            $regex = '/\\?\\?/';
            while(preg_match($regex, $sql) === 1)
            {
                $bind = array_shift($binds);
                
                $compiledBind = is_array($bind)
                    ? $this->columnize2($bind)
                    : $this->wrap($bind);
                
                $sql = preg_replace($regex, $compiledBind, $sql, 1, $count);
            }

            $bindings = array_merge($bindings, $binds);

            return $sql;
        }

        if($value instanceof QueryBuilder)
        {
            $iQueryCompiler = new QueryCompiler($this->iClient, $value, []);

            // $sql = '(' . $iQueryCompiler->toSQL('select')->toString(true, $this->iQueryBuilder, $this->iClient) . ')';
            $sql = $iQueryCompiler->toSQL('select')->toString(true, $this->iQueryBuilder, $this->iClient);

            $this->bindings = array_merge($this->bindings, $iQueryCompiler->getBindings());

            return $sql;
        }

        if($value === null) return 'NULL';

        $bindings[] = $value;

        return '?';
        // return $value;
    }

    private function wrapIdentifier(string $identifier): string
    {// 2023-05-10
        return $this->iClient->wrapIdentifier(trim($identifier), 'query');
    }

    // If we haven't specified any columns or a `tableName`, we're assuming this
    // is only being used for unions.
    private function hasOnlyUnions(): bool
    {// 2023-05-10
        if(count($this->iStatementsGroupedOnType['Columns'] ?? []) !== 0) return false;
        if(count($this->iStatementsGroupedOnType['Union'] ?? []) === 0) return false;
        // if($this->iQueryBuilder->getTableName() !== null) return false;
        
        return true;
    }
    /**
     * @param callable(): mixed $callback
     */
    private function compileCallback(callable $callback, ?string $method=null, ?Client $iClient=null, array &$bindings=[]): Query
    {// 2023-05-10
        $iQueryBuilder = new QueryBuilder($this->iClient, $this->iQueryBuilder->getSchema());

        $callback($iQueryBuilder);

        $iQueryCompiler = new QueryCompiler($iClient ?? $this->iClient, $iQueryBuilder, []);

        $iQuery = $iQueryCompiler->toSQL($method ?? 'select');

        $bindings = array_merge($bindings, $iQuery->getBindings());

        return $iQuery;
    }

    // private function compileColumns(Columns $iColumns, array &$bindings): string
    /*
     *  ->select(['baz', ['bar'=>'foo']])
     *  ->select('baz')
     *  ->select('bar')->as('foo')
     *  ->select('bar as foo')
     *  ->select(new Raw('bar as foo'))
     *  ->select(function($q){ $q->select('bar')->as('foo')->from('baz')->first(); })
     *  ->select(function($q){ $q->select('bar')->as('foo')->from('baz')->first(); })->as('foo')
     *  ->select($iQueryBuilder)
     *  ->select($iQueryBuilder)->as('foo')
     */

    public function compileFrom($tables): string
    {// 2023-05-15
        if(!is_array($tables)) $tables = [$tables];

        $tables = Utilities::arrayRemoveFalsey($tables);

        if(count($tables) === 0) return '';

        foreach($tables as &$table)
        {
            if($this->iSingle->schema !== null) $table = $this->iSingle->schema.'.'.$table;
        }

        $query = $this->columnize2($tables);

        return " FROM {$query}";
    }

    private function hintComments(): string
    {// 2023-05-15
        $iHintComments = $this->iStatementsGroupedOnType['HintComments'] ?? [];
        $hints = array_map(fn($iHintComment) => implode(' ', $iHintComment->getComments()), $iHintComments);

        $hints = Utilities::arrayRemoveFalsey($hints);

        $hints = implode(' ', $hints);

        if($hints === '') return '';

        return "/*+ {$hints} */ ";
    }

    public function compileWhere(): string
    {// 2023-05-15
        $iWheres = $this->iStatementsGroupedOnType['Where'] ?? [];

        $boolMap =
        [
            Where::BOOL_TYPE_AND => 'AND',
            Where::BOOL_TYPE_OR => 'OR',
        ];

        $sql = [];
        foreach($iWheres as $i=>$iWhere)
        {
            $value = null;

                 if($iWhere->getType() === Where::TYPE_BASIC) $value = $this->whereBasic($iWhere);
            else if($iWhere->getType() === Where::TYPE_RAW) $value = $this->whereRaw($iWhere);
            else if($iWhere->getType() === Where::TYPE_WRAPPED) $value = $this->whereWrapped($iWhere);
            else if($iWhere->getType() === Where::TYPE_EXISTS) $value = $this->whereExists($iWhere);
            else if($iWhere->getType() === Where::TYPE_IN) $value = $this->whereIn($iWhere);
            else if($iWhere->getType() === Where::TYPE_NULL) $value = $this->whereNull($iWhere);
            else if($iWhere->getType() === Where::TYPE_BETWEEN) $value = $this->whereBetween($iWhere);
            else if($iWhere->getType() === Where::TYPE_LIKE) $value = $this->whereLike($iWhere);
            else if($iWhere->getType() === Where::TYPE_ILIKE) $value = $this->whereILike($iWhere);
            else if($iWhere->getType() === Where::TYPE_COLUMN) $value = $this->whereColumn($iWhere);

            if($value === null) throw new \Exception('Invalid where type "'.$iWhere->getType().'"');

            $glue = (count($sql) === 0)
                ? 'WHERE'
                : $boolMap[$iWhere->getBoolType()];

            if(!$value) continue;
            
            $sql[] = $glue;
            $sql[] = $value;
        }

        return implode(' ', $sql);
    }

    private function whereBasic(Where $iWhere): string
    {// 2023-05-16
        if($iWhere->getType() !== Where::TYPE_BASIC) throw new \Exception('Invalid where type');

        $notFunction = $iWhere->isNot()
            ? 'NOT '
            : '';

        $column = $this->wrap($iWhere->getColumn());
        $operator = $this->operator($iWhere->getOperator() ?? '=');
        $value = $this->parameter($iWhere->getValue(), $this->bindings);

        if(is_callable($iWhere->getValue())) $value = "({$value})";

        $sql = "{$notFunction}{$column} {$operator} {$value}";

        return $sql;
    }

    private function whereRaw(Where $iWhere): string
    {// 2023-05-16
        if($iWhere->getType() !== Where::TYPE_RAW) throw new \Exception('Invalid where type');

        $sql = $this->unwrapRaw($iWhere->getValue(), $this->bindings);

        return $sql;
    }
    
    private function whereWrapped(Where $iWhere): string
    {// 2023-05-16
        if($iWhere->getType() !== Where::TYPE_WRAPPED) throw new \Exception('Invalid where type');

        $notFunction = $iWhere->isNot()
            ? 'NOT '
            : '';

        $callback = $iWhere->getValue();

        $iQuery = $this->compileCallback($callback, 'where', $this->iClient, $this->bindings);

        $sql = $iQuery->toString(true, $this->iQueryBuilder, $this->iClient);

        $sql = substr($sql, 6);

        if($sql) return "{$notFunction}({$sql})";

        return '';
    }
    
    private function whereExists(Where $iWhere): string
    {// 2023-05-16
        if($iWhere->getType() !== Where::TYPE_EXISTS) throw new \Exception('Invalid where type: '.$iWhere->getType());

        $existsFunction = $iWhere->isNot()
            ? 'NOT EXISTS'
            : 'EXISTS';

        $value = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$existsFunction}({$value})";
    }
    
    private function whereIn(Where $iWhere): string
    {// 2023-05-16
        if($iWhere->getType() !== Where::TYPE_IN) throw new \Exception('Invalid where type');

        $column = $iWhere->getColumn();
        $values = $iWhere->getValue();

        $inFunction = $iWhere->isNot()
            ? 'NOT IN'
            : 'IN';

        // $valuesClause = $this->parametize($values, $this->bindings);
        $valuesClause = $this->values($values, $this->bindings);
        $columnClause = is_array($column) ? "({$this->columnize2($column)})" : $this->wrap($column);

        return "{$columnClause} {$inFunction}{$valuesClause}";
    }
    
    private function whereNull(Where $iWhere): string
    {// 2023-05-16
        $isFunction = $iWhere->isNot()
            ? 'IS NOT'
            : 'IS';

        $column = $this->wrap($iWhere->getColumn());

        return "{$column} {$isFunction} NULL";
    }
    
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

    private function whereLike(Where $iWhere): string
    {// 2023-05-16
        $column = $this->wrap($iWhere->getColumn());
        $value = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$column} LIKE {$value} COLLATE utf8_bin";
    }

    private function whereILike(Where $iWhere): string
    {// 2023-05-16
        $column = $this->wrap($iWhere->getColumn());
        $value = $this->parameter($iWhere->getValue(), $this->bindings);

        return "{$column} LIKE {$value}";
    }

    private function whereColumn(Where $iWhere): string
    {// 2023-06-02
        if($iWhere->getType() !== Where::TYPE_COLUMN) throw new \Exception('Invalid where type: '.$iWhere->getType());

        $column = $this->wrap($iWhere->getColumn());
        $operator = $this->operator($iWhere->getOperator() ?? '=');
        $value = $this->wrap($iWhere->getValue());

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    /**
     * @param mixed $value
     */
    private function wrap($value): string
    {// 2023-05-15
        if($value instanceof Raw || $value instanceof QueryBuilder) return $this->unwrapRaw($value, $this->bindings);

        if(is_callable($value)) return $this->compileCallback($value, null, null, $this->bindings);

        return $this->wrapIdentifier($value.'');
    }
    /**
     * @param mixed $value
     */
    private function operator($value)
    {// 2023-05-15
        if($value instanceof Raw || $value instanceof QueryBuilder) return $this->unwrapRaw($value);

        $operator = self::QUERY_OPERATORS[trim(strtolower($value ?? ''))] ?? null;

        if($operator === null) throw new \Exception("Invalid operator: {$value}");

        return strtoupper($operator);
    }

    public function columns(): ?string
    {// 2023-05-15
        if($this->hasOnlyUnions()) return null;
        
        $hints = $this->hintComments();
        $isDistinct = false;
        $distinctClause = null;

        $sql = [];
        $iColumns = $this->iStatementsGroupedOnType['Columns'] ?? [];
        foreach($iColumns as $iStatement)
        {
            $isDistinct = $isDistinct || $iStatement->isDistinct();

            if($isDistinct && !$iStatement->isDistinct()) $distinctClause = 'DISTINCT ';

            // if($iStatement->isDistinctOn())
            // {
            //     $distinctClause = $this->distinctOn($iStatement->getValue());
            //     continue;
            // }
            //

            if($iStatement->getType() === Columns::TYPE_PLUCK)
            {
                // $sql = array_merge($sql, $this->pluck($iStatement));
                $sql = array_merge($sql, $this->pluck($iStatement));
            }
            else if($iStatement->getType() === Columns::TYPE_AGGREGATE)
            {
                $sql = array_merge($sql, $this->aggregate($iStatement));
            }
            else if($iStatement->getType() === Columns::TYPE_AGGREGATE_RAW)
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

        if(count($sql) === 0) $sql = ['*']; // Defaulting to selecting everything

        $sql = Utilities::arrayRemoveFalsey($sql);
        $selectSql = implode(', ', $sql);

        $query = "SELECT{$hints} {$distinctClause}{$selectSql}{$this->compileFrom($this->iSingle->table)}";

        return $query;
    }

    public function columnize2(array $columns): string
    {// 2023-05-31
        $query = '';

        if(count($columns) === 0) return $query;
        if(count($columns) === 1 && is_array(reset($columns))) $columns = reset($columns);
        
        $count = 0;
        foreach($columns as $i=>$column)
        {
            if($count++ > 0) $query .= ', ';

            // Has alias
            if(is_string($i))
            {
                $alias = $i;

                $columnStr = is_array($column)
                    ? $this->columnize2($column)
                    : $this->wrap($column);

                $query .= "{$columnStr} AS {$this->wrap($alias)}";

                continue;
            }

            if(is_array($column))
            {
                $query .= $this->columnize2($column);

                continue;
            }

            $query .= $this->wrap($column);
        }

        return $query;
    }

    public function pluck(Columns $iColumns): array
    {// 2023-05-15
        $sql = $this->columnize2($iColumns->getColumns());

        return [$sql];
    }

    public function aggregate(Columns $iColumns): array
    {// 2023-05-15
        $isDistinct = $iColumns->isDistinct();
        $aliasSeperator = 'AS';
        $addAlias = function(string $value, ?string $alias=null) use($aliasSeperator)
        {
            if($alias === null) return $value;

            return "{$value} {$aliasSeperator} {$this->wrap($alias)}";
        };

        $aggregateArray = function(array $columns, ?string $alias=null) use($isDistinct, $iColumns, $addAlias)
        {
            $columns = array_map(fn($column) => $this->wrap($column), $columns);
            $columnsStr = implode(', ', $columns);

            if($isDistinct) $columnsStr = "DISTINCT {$columnsStr}";

            $aggregate = "{$iColumns->getAggregateFunction()}({$columnsStr})";

            return $addAlias($aggregate, $alias);
        };

        /**
         * @param string|Raw $value
         * @param ?string $alias
         */
        $aggregateString = function($value, ?string $alias=null) use($isDistinct, $iColumns, $addAlias)
        {// 2023-05-31
            $columnsStr = $this->wrap($value);

            if($isDistinct) $columnsStr = "DISTINCT {$columnsStr}";

            $aggregate = "{$iColumns->getAggregateFunction()}({$columnsStr})";

            return $addAlias($aggregate, $alias);
        };

        $value = $iColumns->getColumns();

        if(count($value) === 1 && is_integer(key($value))) $value = $value[0];
        
        if(is_array($value))
        {
            if(is_integer(key($value))) return [$aggregateArray($value, $iColumns->getAlias())];

            // Is object
            if($iColumns->hasAlias()) throw new \Exception('When using an object explicit alias can not be used');


            return array_map(function($value, $alias) use($aggregateArray, $aggregateString)
            {
                // if(is_integer($alias)) $alias = null;

                if(is_array($value)) return $aggregateArray($value, $alias);

                return $aggregateString($value, $alias);
            }, array_values($value), array_keys($value));
        }

        return [$aggregateString($value, $iColumns->getAlias())];
    }

    public function aggregateRaw(Columns $iColumns, array &$bindings=[]): string
    {// 2023-05-15
        $rawStatements = [];
        foreach($iColumns->getColumns() as $iRaw)
        {
            $rawStatements[] = $this->unwrapRaw($iRaw, $bindings);
        }

        $distinctClause = $iColumns->isDistinct() ? 'DISTINCT ' : '';
        $statement = implode(', ', $rawStatements);

        $query = "{$iColumns->getAggregateFunction()}({$distinctClause}{$statement})";

        return $query;
    }

    public function select(): string
    {// 2023-05-10
        // With always comes first
        // $query = $this->with();

        $query = '';
        
        $unionStatement = null;
        $firstStatements = [];
        $endStatements = [];
        foreach(self::QUERY_COMPONENTS as $component)
        {
            $statement = $this->{$component}($this);

            switch($component)
            {
                case 'union':
                    $unionStatement = $statement;
                    break;
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

        if($this->hasOnlyUnions())
        {
            $query .= implode(' ',
                Utilities::arrayRemoveFalsey(array_merge(
                    $firstStatements,
                    $endStatements)));
        }
        else
        {
            $mainStatements = Utilities::arrayRemoveFalsey($firstStatements);
            $endStatements = Utilities::arrayRemoveFalsey($endStatements);
            $allStatements = $wrapMainQuery
                ? '('.implode(' ', $mainStatements).')'
                : implode(' ', $mainStatements);

            $endStats = implode(' ', $endStatements);

            $query .=
                $allStatements.
                ($unionStatement ? ' '.$unionStatement : '').
                $endStats;
        }

        return $query;
    }

    public function with(): string
    {// 2023-05-10
        $iWithStatements = $this->iStatementsGroupedOnType['With'] ?? [];

        if(count($iWithStatements) === 0) return '';

        $sqlStrings = [];
        $hasRecursive = false;
        foreach($iWithStatements as $iWithStatement)
        {// 2023-05-10
            $hasRecursive = $hasRecursive || $iWithStatement->isRecursive();

            $sqlStrings[] = call_user_func([$this, $iWithStatement->getType(), $iWithStatement]);
        }
        
        return "WITH " . ($hasRecursive ? 'RECURSIVE ' : '') . implode(', ', $sqlStrings);
    }

    public function withWrapped(With $iWithStatement): string
    {// 2023-05-10
        $sqlString = WrappingFormatter::fnOrRaw($iWithStatement->getValue(), null, $this->iQueryBuilder, $this->iClient, $this->bindings);

        $columnList = $iWithStatement->hasColumnList()
            ? '(' . WrappingFormatter::columnize($iWithStatement->getColumnList(), $this->iQueryBuilder, $this->iClient, $this->bindings) . ')'
            : '';

        $aliasList = WrappingFormatter::columnize($iWithStatement->getAlias(), $this->iQueryBuilder, $this->iClient, $this->bindings);

        return $aliasList . $columnList + 'AS ' + $iWithStatement->getMaterialized() + '(' + $sqlString + ')';
    }

    // public function columnClause(Columns $iColumnStatement): string
    // {// 2023-05-10
    //     return '('.WrappingFormatter::columnize($iColumnStatement->getColumns(), $this->iQueryBuilder, $this->iClient, $this->bindings).')';
    // }

    private function processJoinClause(Clause $iClause): ?string
    {// 2023-06-01
        $value = null;
        if($iClause->getType() === Join::ON_TYPE_RAW) $value = $this->onRaw($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_BASIC) $value = $this->onBasic($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_VALUE) $value = $this->onValue($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_BETWEEN) $value = $this->onBetween($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_WRAPPED) $value = $this->onWrapped($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_USING) $value = $this->onUsing($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_IN) $value = $this->onIn($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_NULL) $value = $this->onNull($iClause, $this->bindings);
        else if($iClause->getType() === Join::ON_TYPE_EXISTS) $value = $this->onExists($iClause, $this->bindings);
            
        return $value;
    }

    public function join()
    {// 2023-05-15
        $sql = '';

        $iJoins = $this->iStatementsGroupedOnType['Join'] ?? [];

        $count = 0;
        foreach($iJoins as $iJoin)
        {
            if($count++ > 0) $sql .= ' ';

            $tableName = $iJoin->getTableName();
            if($this->iSingle->schema !== null) $tableName = $this->iSingle->schema.'.'.$tableName;

            $sql .= ($iJoin->getType() === Join::TYPE_RAW)
                ? $this->unwrapRaw($iJoin->getValue(), $this->bindings)
                : $iJoin->getJoinFunction().' '.$this->wrap($tableName);

            $clausesCount = 0;
            foreach($iJoin->getClauses() as $iClause)
            {
                $sql .= ($clausesCount++ > 0)
                    ? " {$iClause->getBoolFunction()} "
                    : " {$iClause->getOnFunction()} ";

                $value = $this->processJoinClause($iClause);
                
                if($value) $sql .= $value;
            }

        }

        return $sql;
    }

    private function onRaw(Clause $iClause): string
    {// 2023-05-31
        return $this->unwrapRaw($iClause->getValue(), $this->bindings);
    }

    private function onBasic(Clause $iClause): string
    {// 2023-05-31
        $wrap = $iClause->getValue() instanceof QueryBuilder;

        $column = $this->wrap($iClause->getColumn());
        $operator = $this->operator($iClause->getOperator());
        $value = $this->wrap($iClause->getValue()); 

        if($wrap) $value = "({$value})";

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    private function onValue(Clause $iClause): string
    {// 2023-05-31
        $column = $this->wrap($iClause->getColumn());
        $operator = $this->operator($iClause->getOperator());
        $value = $this->parameter($iClause->getValue(), $this->bindings);

        $sql = "{$column} {$operator} {$value}";

        return $sql;
    }

    private function onBetween(Clause $iClause): string
    {// 2023-06-01
        $betweenFunction = $iClause->isNot()
            ? 'NOT BETWEEN'
            : 'BETWEEN';

        $column = $this->wrap($iClause->getColumn());
        $values = array_map(function($value) { return $this->parameter($value, $this->bindings); }, $iClause->getValue());
        $values = implode(' AND ', $values);

        $sql = "{$column} {$betweenFunction} {$values}";

        return $sql;
    }

    private function onWrapped(Clause $iClause): string
    {// 2023-05-31
        $iJoin = new Join($iClause->getValue(), Join::TYPE_RAW);
        $callback = $iClause->getValue();

        $callback($iJoin);

        $sql = '';
        $count = 0;
        foreach($iJoin->getClauses() as $iClause)
        {
            if($count++ > 0) $sql .= " {$iClause->getBoolFunction()} ";

            $val = $this->processJoinClause($iClause);
            if($val) $sql .= $val;
        }

        if($sql) return "({$sql})";

        return $sql;
    }

    private function onUsing(Clause $iClause): string
    {// 2023-05-31
        return "({$this->columnize2([$iClause->getColumn()])})";
    }

    private function onIn(Clause $iClause): string
    {// 2023-05-31
        if(is_array($iClause->getValue()) && is_array($iClause->getValue()[0] ?? 0)) return $this->onInMultiple($iClause);

        $values = null;

        if($iClause->getValue() instanceof Raw) $values = $this->parameter($iClause->getValue(), $this->bindings);
        else $values = $this->parametize($iClause->getValue(), $this->bindings);

        $onFunction = $iClause->isNot()
            ? 'NOT IN'
            : 'IN';

        return "{$this->wrap($iClause->getColumn())} {$onFunction}({$values})";
    }

    private function onInMultiple(Clause $iClause): string
    {// 2023-05-31
        $sql = $this->columnize2([$iClause->getColumn()]);

        $onFunction = $iClause->isNot()
            ? 'NOT IN'
            : 'IN';
        
        $sql .= "{$onFunction} ((";
        $count = 0;
        foreach($iClause->getValue() as $value)
        {
            if($count++ > 0) $sql .= '),(';

            $sql .= $this->parametize($value, $this->bindings);
        }

        return $sql.'))';
    }

    private function onNull(Clause $iClause): string
    {// 2023-05-31
        $column = $this->wrap($iClause->getColumn());

        $isFunction = $iClause->isNot()
            ? 'IS NOT'
            : 'IS';

        $value = 'NULL';

        return "{$column} {$isFunction} {$value}";
    }

    private function onExists(Clause $iClause): string
    {// 2023-05-31
        $existsFunction = $iClause->isNot()
            ? 'NOT EXISTS'
            : 'EXISTS';

        return "{$existsFunction}({$this->parameter($iClause->getValue(), $this->bindings)})";
    }

    public function where()
    {// 2023-05-15
        return $this->compileWhere();
    }

    public function union()
    {// 2023-05-15
        return '';
    }

    public function group()
    {// 2023-05-15
        return '';
    }

    public function having()
    {// 2023-05-15
        return '';
    }

    public function order()
    {// 2023-05-15
        return '';
    }

    public function limit()
    {// 2023-05-15
        $noLimit = $this->iSingle->limit === null;

        if($noLimit) return '';

        $limit = $this->parameter($this->iSingle->limit, $this->bindings);

        return " LIMIT {$limit}";
    }

    public function offset()
    {// 2023-05-15
        $noOffset = $this->iSingle->offset === null;

        if($noOffset) return '';

        $noLimit = $this->iSingle->limit === null;

        $sql = '';
        if($noLimit)
        {
            $this->iSingle->limit = 18446744073709551615;
            $sql = $this->limit().' ';
        }

        $offset = $this->parameter($this->iSingle->offset, $this->bindings);

        return "{$sql}OFFSET {$offset}";
    }

    public function lock()
    {// 2023-05-15
        return '';
    }

    public function waitMode()
    {
        return '';
    }

    // Checks whether a value is a function... if it is, we compile it
    // otherwise we check whether it's a raw
    private function parameter($value, array &$bindings)
    {// 2023-05-31
        if(is_callable($value)) return $this->compileCallback($value, null, null, $bindings)->toString(true, $this->iQueryBuilder, $this->iClient);

        return $this->unwrapRaw($value, $bindings) ?? '?';
    }

    private function parametize($values, array &$bindings): string
    {// 2023-05-31
        if(is_callable($values)) return $this->parameter($values, $bindings);

        if(!is_array($values)) $values = [$values];

        $sql = '';
        $count = 0;
        foreach($values as $value)
        {
            if($count++ > 0) $sql .= ', ';

            $sql .= $this->parameter($value, $bindings);
        }

        return $sql;
    }

    // Formats `values` into a parenthesized list of parameters for a `VALUES`
    // clause.
    //
    // [1, 2]                  -> '(?, ?)'
    // [[1, 2], [3, 4]]        -> '((?, ?), (?, ?))'
    // knex('table')           -> '(select * from "table")'
    // knex.raw('select ?', 1) -> '(select ?)'
    //
    private function values($values, array &$bindings): string
    {// 2023-06-02
        if($values instanceof Raw || $values instanceof QueryBuilder) return "({$this->parameter($values, $bindings)})";

        if(is_array($values))
        {
            if(!is_array($values[0] ?? 0)) return "({$this->parametize($values, $bindings)})";

            // $sqlValues = array_map(fn($value) => "({$this->parametize($value, $bindings)})", $values);
            $sqlValues = [];
            foreach($values as $value) $sqlValues[] = "({$this->parametize($value, $bindings)})";
            $sqlValues = implode(', ', $sqlValues);

            return "({$sqlValues})";
        }

        return "({$this->parameter($values, $bindings)})";
    }
}

