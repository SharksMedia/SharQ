<?php
/**
 * Class QueryBuilder
 * 2023-05-08
 *
 * @author      Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

use Sharksmedia\QueryBuilder\Client;
use Sharksmedia\QueryBuilder\Single;
use Sharksmedia\QueryBuilder\OnConflictBuilder;

use Sharksmedia\QueryBuilder\Statement\IStatement;

use Sharksmedia\QueryBuilder\Statement\Comments;
use Sharksmedia\QueryBuilder\Statement\With;
use Sharksmedia\QueryBuilder\Statement\Columns;
use Sharksmedia\QueryBuilder\Statement\HintComments;
use Sharksmedia\QueryBuilder\Statement\Join;
use Sharksmedia\QueryBuilder\Statement\Where;
use Sharksmedia\QueryBuilder\Statement\Having;
use Sharksmedia\QueryBuilder\Statement\Group;
use Sharksmedia\QueryBuilder\Statement\Order;
use Sharksmedia\QueryBuilder\Statement\Union;
use Sharksmedia\QueryBuilder\Statement\Raw;

class QueryBuilder
{
    public const BOOL_TYPE_AND = 'AND';
    public const BOOL_TYPE_OR = 'OR';
    
    public const METHOD_SELECT = 'SELECT';
    public const METHOD_FIRST = 'FIRST';
    public const METHOD_PLUCK = 'PLUCK';
    public const METHOD_INSERT = 'INSERT';
    public const METHOD_UPDATE = 'UPDATE';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_TRUNCATE = 'TRUNCATE';

    public const LOCK_MODE_FOR_UPDATE = 'FOR UPDATE';
    public const LOCK_MODE_FOR_SHARE = 'FOR SHARE';
    public const LOCK_MODE_FOR_NO_KEY_UPDATE = 'FOR NO KEY UPDATE';
    public const LOCK_MODE_FOR_KEY_SHARE = 'FOR KEY SHARE';

    public const WAIT_MODE_SKIP_LOCKED = 'SKIP LOCKED';
    public const WAIT_MODE_NO_WAIT = 'NO WAIT';

    public const CLEARABLE_STATEMENTS =
    [
        'with',
        'select',
        'columns',
        'hintComments',
        'where',
        'union',
        'join',
        'group',
        'order',
        'having',
        'limit',
        'offset',
        'counter',
        'counters',
    ];


    /**
     * This is the iClient attribute.
     * @var Client
     */
    private Client $iClient;

    /**
     * This is the iStatements attribute.
     * @var IStatement[]
     */
    private array $iStatements = [];

    /**
     * This is the method attribute.
     * see QueryBuilder::METHOD_* constants
     * @var string
     */
    private string $method = self::METHOD_SELECT;

    /**
     * This is the iSingle attribute.
     * @var Single
     */
    private Single $iSingle;

    /**
     * This is the schema attribute.
     * @var string
     */
    private string $schema;

    /**
     * This is the joinFlag attribute.
     * see Join::TYPE_* constants
     * @var string
     */
    private string $joinFlag = Join::TYPE_INNER;

    /**
     * This is the whereFlag attribute.
     * see Where::TYPE_* constants
     * @var string
     */
    private string $whereFlag = Where::TYPE_BASIC;

    /**
     * This is the boolType attribute.
     * see QueryBuilder::BOOL_TYPE_* constants
     * @var string
     */
    private string $boolType = self::BOOL_TYPE_AND;

    /**
     * This is the isNot attribute.
     * @var bool
     */
    private bool   $isNot = false;

    public function __construct(Client $iClient, ?string $schema=null)
    {// 2023-05-08
        $this->iClient = $iClient;
        $this->schema = $schema ?? $iClient->getConfig()->getDatabase();
        $this->iSingle = new Single();
    }

    /**
     * 2023-06-12
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->iClient;
    }

    /**
     * This is the getSchema method.
     * @return string Returns the schema.
     */
    public function getSchema(): string
    {// 2023-05-10
        return $this->schema;
    }

    /**
     * This is the getSchema method.
     * see Columns::TYPE_* constants
     * @return string|null Returns the select method used.
     */
    public function getSelectMethod(): ?string
    {// 2023-05-15
        return $this->iSingle->columnMethod;
    }

    /**
     * This is the getMethod method.
     * see QueryBuilder::METHOD_* constants
     * @return string Returns the method used.
     */
    public function getMethod(): string
    {// 2023-06-05
        return $this->method;
    }

    /**
     * Clears a specific grouping.
     * @param string $type
     * @return QueryBuilder
     */
    private function clearGrouping(string $type): self
    {// 2023-05-15
        $this->iStatements = array_filter($this->iStatements, function($statement) use($type)
        {
            return !($statement instanceof $type);
        });

        return $this;
    }

    /**
     * Clears ->with statements
     * @return QueryBuilder
     */
    public function clearWith(): self
    {// 2023-05-15
        return $this->clearGrouping(With::class);
    }

    /**
     * Clears ->select statements
     * @return QueryBuilder
     */
    public function clearSelect(): self
    {// 2023-05-15
        return $this->clearGrouping(Columns::class);
    }

    /**
     * Clears ->join statements
     * @return QueryBuilder
     */
    public function clearJoin(): self
    {// 2023-05-15
        return $this->clearGrouping(Join::class);
    }

    /**
     * Clears ->union statements
     * @return QueryBuilder
     */
    public function clearUnion(): self
    {// 2023-05-15
        return $this->clearGrouping(Union::class);
    }

    /**
     * Clears ->hintComment statements
     * @return QueryBuilder
     */
    public function clearHintComments(): self
    {// 2023-05-15
        return $this->clearGrouping(HintComments::class);
    }

    /**
     * Clears ->where statements
     * @return QueryBuilder
     */
    public function clearWhere(): self
    {// 2023-06-01
        return $this->clearGrouping(Where::class);
    }

    /**
     * Clears ->increment, ->decrement statements
     * @return QueryBuilder
     */
    public function clearCounters(): self
    {// 2023-06-01
        $this->iSingle->counter = null;
        return $this;
    }

    /**
     * Clears ->groupBy statements
     * @return QueryBuilder
     */
    public function clearGroup(): self
    {// 2023-06-01
        return $this->clearGrouping(Group::class);
    }

    /**
     * Clears ->orderBy statements
     * @return QueryBuilder
     */
    public function clearOrder(): self
    {// 2023-06-01
        return $this->clearGrouping(Order::class);
    }

    /**
     * Clears ->having statements
     * @return QueryBuilder
     */
    public function clearHaving(): self
    {// 2023-06-01
        return $this->clearGrouping(Having::class);
    }

    /**
     * Clears ->limit statements
     * @return QueryBuilder
     */
    public function clearLimit(): self
    {// 2023-06-01
        $this->iSingle->limit = null;
        return $this;
    }

    /**
     * Clears ->offset statements
     * @return QueryBuilder
     */
    public function clearOffset(): self
    {// 2023-06-01
        $this->iSingle->offset = null;
        return $this;
    }

    /**
     * Generic clear function. Clears statements
     * possible values: 'with', 'select', 'columns', 'hintComments', 'where', 'union', 'join', 'group', 'order', 'having', 'limit', 'offset', 'counter', 'counters'
     *
     * @param string $statementName
     * @return QueryBuilder
     */
    public function clear(string $statementName): self
    {// 2023-06-07
        $statementMap =
        [
            'with'=>'clearWith',
            'select'=>'clearSelect',
            'columns'=>'clearSelect',
            'hintComments'=>'clearHintComments',
            'where'=>'clearWhere',
            'union'=>'clearUnion',
            'join'=>'clearJoin',
            'group'=>'clearGroup',
            'order'=>'clearOrder',
            'having'=>'clearHaving',
            'limit'=>'clearLimit',
            'offset'=>'clearOffset',
            'counter'=>'clearCounters',
            'counters'=>'clearCounters',
        ];

        $statementClearingFunction = $statementMap[$statementName] ?? null;

        if($statementClearingFunction === null) throw new \Exception('Unknown statement: '.$statementName);

        $this->{$statementClearingFunction}();

        return $this;
    }

    /**
     * 2023-05-08
     * Get single options
     * @return Single
     */
    public function &getSingle(): Single
    {// 2023-05-15
        return $this->iSingle;
    }

    /**
     * @param string|Raw|QueryBuilder|callable $arg
     * @return bool
     */
    private function isValidStatementArg($arg): bool
    {// 2023-06-07
        return is_callable($arg) || $arg instanceof Raw || $arg instanceof QueryBuilder;
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement, method]
     */
    private function validateWithArgs(string $alias, ...$args): void
    {// 2023-06-07
        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement = $args[1] ?? null;
        $method = $args[2] ?? null;

        if(func_num_args() === 2) if($this->isValidStatementArg($statementOrColumnList)) return;

        $isNonEmptyList = is_array($statementOrColumnList)
                          &&
                          count($statementOrColumnList) !== 0
                          &&
                          array_reduce($statementOrColumnList, fn($carry, $item) => $carry && is_string($item), true);

        if(!$isNonEmptyList) throw new \Exception('Invalid with statement arguments');

        if($this->isValidStatementArg($nothingOrStatement)) return;

        throw new \Exception("{$method}() third argument must be a function / QueryBuilder or a raw when its second argument is a column name list");
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement, method]
     */
    public function with(string $alias, ...$args): QueryBuilder
    {// 2023-05-15
        $this->validateWithArgs($alias, ...$args);

        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement = $args[1] ?? null;
        // $method = $args[2] ?? null;

        return $this->withWrapped($alias, $statementOrColumnList, $nothingOrStatement);
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement]
     */
    public function withWrapped(string $alias, ...$args): QueryBuilder
    {// 2023-05-15

        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement = $args[1] ?? null;

        $statement = null;
        $columnList = null;

        if($this->isValidStatementArg($statementOrColumnList)) $statement = $statementOrColumnList;
        else
        {
            $columnList = $statementOrColumnList;
            $statement = $nothingOrStatement;
        }

        $iWith = new With($alias, $columnList, $statement);

        $this->iStatements[] = $iWith;

        return $this;
    }

    /**
     * @param string|Raw|QueryBuilder|array<int, string|Raw|QueryBuilder> $tableName
     */
    public function table($tableName): QueryBuilder
    {// 2023-05-15
        if(!is_array($tableName)) $tableName = [$tableName];

        $tableName = $this->_normalizeColumns([$tableName]);

        $this->iSingle->table = $tableName;

        return $this;
    }

    /**
     * @param string|Raw|QueryBuilder|array<int, string|Raw|QueryBuilder> $tableName
     */
    public function from($tableName): QueryBuilder
    {// 2023-05-15
        return $this->table($tableName);
    }

    /**
     * @param string $raw
     */
    public function fromRaw(string $raw): QueryBuilder
    {// 2023-05-15
        return $this->table(new Raw($raw));
    }

    /**
     * @param string|Raw|QueryBuilder|array<int, string|Raw|QueryBuilder> $tableName
     */
    public function into($tableName): QueryBuilder
    {// 2023-05-15
        return $this->table($tableName);
    }

    /**
     * @param string|Raw|null $schemaName
     */
    public function withSchema($schemaName): QueryBuilder
    {// 2023-05-26
        $this->iSingle->schema = $schemaName;
        return $this;
    }

    /**
     * 2023-05-08
     * How long to wait for query to complete
     * @param int $milliSeconds
     * @param bool $cancel Cancel query if timeout is reached. (default: false). ie. if true, the query will not throw
     * @throws Exception\QueryTimeoutException
     * @return self
     */
    public function timeout(int $milliSeconds, bool $cancel=false): QueryBuilder
    {// 2023-05-08
        // 2023-05-08 TODO: implement me
        if($milliSeconds < 0) throw new \UnexpectedValueException('Timeout must be a positive integer');

        return $this;
    }

    /**
     * 2023-05-08
     * @return IStatement[]
     */
    public function getStatements(): array
    {// 2023-05-10
        return $this->iStatements;
    }

    /**
     * 2023-05-08
     * @param array<int|string, string|Raw|QueryBuilder> $columns One or more values
     * @return QueryBuilder
     */
    public function column(...$columns): QueryBuilder
    {// 2023-05-08
        return $this->_column($columns, Columns::TYPE_PLUCK);
    }

    /**
     * @param array<int,mixed> $columns
     * @return array<int,mixed>|mixed|bool
     */
    private function _normalizeColumns(array $columns): array
    {
        if(count($columns) === 1)
        {
            $col = reset($columns);
            if(is_array($col) && is_integer(key($col)))
            {
                $columns = $col;
            }
        }
        
        foreach($columns as $i=>$column)
        {
            if(is_string($column) && is_integer($i))
            {
                // 2023-05-30 Handling cases where column is aliased in a string ie. ' foo  aS bar'
                preg_match('/(\\S+)\\s+as\\s+(\\S+)/i', trim($column), $matches);
                
                if(count($matches) === 3)
                {
                    $columns[$i] = [$matches[2]=>$matches[1]];
                }
            }
        }

        // if(count($columns) === 0) $columns = ['*']; // 2023-06-07 fixing unions

        return $columns;
    }

    /**
     * see Column::TYPE_* constants
     * @param array<int,mixed> $columns
     * @param string $type
     */
    private function _column(array $columns, string $type): QueryBuilder
    {// 2023-05-15
        $columns = $this->_normalizeColumns($columns);
        
        $iColumns = new Columns(null, $columns, $type);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * 2023-05-08
     * @param array<int, string|Raw|QueryBuilder> $columns One or more values
     * @return QueryBuilder
     */
    public function distinct(string ...$columns): QueryBuilder
    {// 2023-05-08
        $columns = $this->_normalizeColumns($columns);

        $iColumns = new Columns(null, $columns);
        $iColumns->distinct(true);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * 2023-05-08
     * @param array<int, string|Raw|QueryBuilder> $columns One or more values
     * @return QueryBuilder
     */
    public function distinctOn(string ...$columns): QueryBuilder
    {// 2023-05-08
        $columns = $this->_normalizeColumns($columns);

        $iColumns = new Columns(null, $columns);
        $iColumns->distinctOn(true);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * 2023-05-08
     * @param string $alias
     * @return QueryBuilder
     */
    public function as(string $alias): QueryBuilder
    {// 2023-05-09
        $this->iSingle->alias = $alias;

        return $this;
    }

    /**
     * Returns the query alias
     * @return string|null
     */
    public function getAlias(): ?string
    {// 2023-06-08
        return $this->iSingle->alias;
    }
    
    /**
     * Returns wether the query has an alias
     * @return bool
     */
    public function hasAlias(): bool
    {// 2023-06-08
        return !empty($this->iSingle->alias);
    }

    /**
     * 2023-05-08
     * @param string[] $hintComments One or more values
     * @return QueryBuilder
     */
    public function hintComment(...$hintComments): QueryBuilder
    {// 2023-05-09
        $iHintComments = new HintComments($hintComments);

        $this->iStatements[] = $iHintComments;

        return $this;
    }

    /**
     * 2023-05-08
     * @param string[] $comments One or more values
     * @return QueryBuilder
     */
    public function comment(...$comments): QueryBuilder
    {// 2023-05-09
        $iComments = new Comments($comments);

        $this->iStatements[] = $iComments;

        return $this;
    }

    /**
     * Sets the values for a `select` query
     * @param array<int, string|Raw|QueryBuilder> $columns One or more values
     * @return QueryBuilder
     */
    public function select(...$columns): QueryBuilder
    {// 2023-05-15
        $this->iSingle->columnMethod = Columns::TYPE_PLUCK;

        return $this->_column($columns, Columns::TYPE_PLUCK);
    }

    /**
     * Sets the values for a `select` query, informing that only the first row should be returned (limit 1).
     * @param array<int, string|Raw|QueryBuilder> $columns One or more values
     * @return QueryBuilder
     */
    public function first(...$columns): QueryBuilder
    {// 2023-05-15
        $this->iSingle->columnMethod = Columns::TYPE_FIRST;

        if(count($columns) !== 0) $this->_column($columns, Columns::TYPE_PLUCK);

        return $this->limit(1);
    }

    /**
     * 2023-05-08
     *
     * join(string $table, string $first, string $operator, string $second)
     * join(string $table, callable $first)
     *
     * @param string|Raw $table
     * @param string|Raw|QueryBuilder|callable $first on statement [column]
     * @param array<int,string|Raw|QueryBuilder> $args on statement [operator, value]
     * @return QueryBuilder
     */
    public function join($table, $first=null, ...$args): QueryBuilder
    {// 2023-05-09
        $iJoin = null;

        $tableParts = is_string($table)
            ? preg_split('/ AS /i', $table)
            : [$table];

        $table = array_shift($tableParts);
        $alias = array_shift($tableParts);
        
        if(is_callable($first))
        {
            $iJoin = new Join($table, $this->joinFlag, $this->schema);
            $first($iJoin);
        }
        else if($this->joinFlag === Join::TYPE_RAW)
        {
            $iJoin = new Join(new Raw($table, $first), Join::TYPE_RAW);
        }
        // else if($this->joinFlag === Join::TYPE_CROSS)
        // {
        //     $iJoin = new Join($table, Join::TYPE_CROSS, $this->schema);
        // }
        else
        {
            $iJoin = new Join($table, $this->joinFlag, $this->schema);
            if($first) $iJoin->on($first, ...$args);
        }

        if($alias) $iJoin->as($alias);

        $this->iStatements[] = $iJoin;

        return $this;
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function innerJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_INNER;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function leftJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_LEFT;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function leftOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_LEFT_OUTER;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function rightJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RIGHT;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function rightOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RIGHT_OUTER;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function outerJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_OUTER;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function fullOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_FULL_OUTER;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function crossJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_CROSS;
        return $this->join(...$args);
    }

    /**
     * @param array<int,string|Raw|QueryBuilder> $args on statement [column, operator, value]
     * @return QueryBuilder
     */
    public function joinRaw(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RAW;
        return $this->join(...$args);
    }

    /**
     * Where modifier. Changes bool type to OR
     * @return QueryBuilder
     */
    public function or(): self
    {// 2023-05-09
        $this->boolType = self::BOOL_TYPE_OR;
        return $this;
    }

    /**
     * Where modifier. Changes not type
     * @return QueryBuilder
     */
    public function not(): self
    {// 2023-05-09
        $this->isNot = true;
        return $this;
    }

    /**
     * @param string|Raw|QueryBuilder|callable $args [column, operator, value]
     * @return QueryBuilder
     */
    public function where(...$args): QueryBuilder
    {// 2023-05-09
        $column = $args[0] ?? null;
        $operator = $args[1] ?? null;
        $value = $args[2] ?? null;

        $argCount = func_num_args();
        // $args = array_filter(func_get_args(), fn($value) => !is_null($value));
        // $argCount = count($args);

        // Check if the column is a function, in which case it's
        // a where statement wrapped in parens.
        if(is_callable($column)) return $this->whereWrapped($column);

        // Check if the column is an array, in which case it's multiple wheres
        if(is_array($column))
        {
            if(count($column) === 1)
            {
                $col = key($column);
                $value = reset($column);
                // If the first value in the array is an integer, we will assume that
                // the developer wants to run a where-in statement with a basic SQL
                // clause. The values in the array will be the only values placed
                // in the "IN" clause, while the columns are specified in SQL.
                return $this->where($col, '=', $value);
            }

            if($this->boolType === self::BOOL_TYPE_AND)
            {
                foreach($column as $columnName=>$value)
                {
                    // $this->andWhere($columnName, '=', $value);
                    $this->andWhere($columnName, '=', $value);
                }

                return $this;
            }
            
            $this->where(function($q) use($column)
            {
                foreach($column as $columnName=>$value)
                {
                    // $this->andWhere($columnName, '=', $value);
                    $q->andWhere($columnName, '=', $value);
                }
            });

            return $this;
        }

        if($argCount === 1)
        {
            // Allow a raw statement to be passed along to the query.
            if($column instanceof Raw) return $this->whereRaw($column->getSQL(), ...$column->getBindings());

            // Support "where true || where false"
            if(is_bool($column)) return $this->whereRaw(($column ? '1' : '0').' = 1');
        }

        if($argCount === 2)
        {
            // Push onto the where statement stack.
            // operator is assumed to be value
            // $iWhere = new Where($column, null, $operator, $this->boolType, $this->isNot, Where::TYPE_BASIC);

            if(is_null($operator)) return $this->_whereNull($column);
            if(is_bool($operator)) $operator = (int)$operator;

            $iWhere = new Where($column, null, $operator, $this->boolType, $this->isNot, $this->whereFlag ?? Where::TYPE_BASIC);

            $this->iStatements[] = $iWhere;

            return $this;
        }

        if($argCount === 3)
        {
            // lower case the operator for comparison purposes
            $checkOperator = strtolower(trim($operator ?? ''));

            if(in_array($checkOperator, ['in', 'not in']))
            {
                $this->isNot = $checkOperator === 'not in';
                return $this->whereIn($column, $value);
            }

            if(in_array($checkOperator, ['between', 'not between']))
            {
                $this->isNot = $checkOperator === 'not between';
                return $this->_whereBetween($column, $value);
            }
        }

        // If the value is still null, check whether they're meaning, where value is null
        if($value === null)
        {
            if(in_array($operator, ['is', 'is not', '=', '!=']))
            {
                $this->isNot = $checkOperator === 'is not' || $checkOperator === '!=';
                return $this->whereNull($column);
            }
        }

        // Push onto the where statement stack.
        $iWhere = new Where($column, $operator, $value, $this->boolType, $this->isNot, $this->whereFlag ?? Where::TYPE_BASIC);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw|QueryBuilder|callable $args [column, operator, value]
     * @return QueryBuilder
     */
    public function whereColumn(...$args): QueryBuilder
    {// 2023-06-01
        $this->whereFlag = Where::TYPE_COLUMN;
        return $this->where(...$args);
    }

    /**
     * @param array<int, string|Raw|QueryBuilder|callable> $args [column, operator, value]
     * @return QueryBuilder
     */
    public function andWhere(...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->where(...$args);
    }

    /**
     * @param array<int, string|Raw|QueryBuilder|callable> $args [column, operator, value]
     * @return QueryBuilder
     */
    public function orWhere(...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->where(...$args);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $args [column, operator, value]
     * @return QueryBuilder
     */
    public function whereNot(...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->isNot = true;
        return $this->where(...$args);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $args [column, operator, value]
     * @return QueryBuilder
     */
    public function orWhereNot(...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = true;
        return $this->where(...$args);
    }

    /**
     * @param array<int,mixed> $bindings
     * @return QueryBuilder
     */
    public function whereRaw(string $sql, ...$bindings): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_RAW;

        $iRaw = new Raw($sql, ...$bindings);
        $iWhere = new Where(null, null, $iRaw, $this->boolType, $this->isNot, Where::TYPE_RAW);

        $this->iStatements[] = $iWhere;

        return $this;
    }
    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function whereWrapped(callable $callback): QueryBuilder
    {// 2023-05-09
        $iQueryBuilder = new QueryBuilder($this->iClient, $this->schema);
        $callback($iQueryBuilder);

        $iWhere = new Where(null, null, $callback, $this->boolType, $this->isNot, Where::TYPE_WRAPPED);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    private function _whereExists($value): QueryBuilder
    {// 2023-06-02
        $iWhere = new Where(null, null, $value, $this->boolType, $this->isNot, Where::TYPE_EXISTS);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param callable|QueryBuilder $callback
     * @return QueryBuilder
     */
    public function whereExists($callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereExists($callback);
    }

    /**
     * @param callable|QueryBuilder $callback
     * @return QueryBuilder
     */
    public function whereNotExists($callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereExists($callback);
    }

    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function orWhereExists(callable $callback): QueryBuilder
    {// 2023-05-07
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereExists($callback);
    }

    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function orWhereNotExists(callable $callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereExists($callback);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function whereIn($column, $values): QueryBuilder
    {// 2023-05-09
        if(is_array($values) && count($values) === 0)
        {
            $bool = $this->isNot;
            $this->isNot = false;

            return $this->where($bool);
        }
        // if(is_array($column) && count($column) !== count($values[0])) throw new \Exception('The number of columns does not match the number of values');

        $this->whereFlag = Where::TYPE_IN;

        $iWhere = new Where($column, null, $values, $this->boolType, $this->isNot, Where::TYPE_IN);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function whereNotIn($column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->whereIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function andWhereIn($column, $values): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_IN;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->whereIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function andWhereNotIn($column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->andWhereIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function orWhereIn($column, $values): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_IN;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->whereIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function orWhereNotIn($column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->orWhereIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    private function _whereNull($column): QueryBuilder
    {// 2023-06-02
        $iWhere = new Where($column, null, null, $this->boolType, $this->isNot, Where::TYPE_NULL);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function whereNull($column): QueryBuilder
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull($column);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function whereNotNull($column): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull($column);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orWhereNull($column): QueryBuilder
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull($column);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orWhereNotNull($column): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull($column);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @param string|Raw|QueryBuilder|callable $type
     * @return QueryBuilder
     */
    private function _whereLike($column, $value, $type): QueryBuilder
    {// 2023-06-01
        $iWhere = new Where($column, null, $value, $this->boolType, $this->isNot, $type);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function whereLike($column, $value): QueryBuilder
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function andWhereLike($column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function orWhereLike($column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function whereILike($column, $value): QueryBuilder
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function andWhereILike($column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $value
     * @return QueryBuilder
     */
    public function orWhereILike($column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    private function _whereBetween($column, $values): QueryBuilder
    {// 2023-06-01
        if(count($values) !== 2) throw new \InvalidArgumentException('whereBetween() expects exactly 2 values');

        $iWhere = new Where($column, null, $values, $this->boolType, $this->isNot, Where::TYPE_BETWEEN);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function whereBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        return $this->_whereBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function whereNotBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        return $this->_whereBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function andWhereBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function andWhereNotBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function orWhereBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder|callable> $values
     * @return QueryBuilder
     */
    public function orWhereNotBetween($column, $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereBetween($column, $values);
    }

    /**
     * Helper for compiling any aggregate queries.
     * @param string|Raw $method
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    private function aggregate($method, $column, array $options=[]): QueryBuilder
    {// 2023-05-26
        $type = Columns::TYPE_AGGREGATE;
        if($column instanceof Raw) $type = Columns::TYPE_AGGREGATE_RAW;

        if(!is_array($column)) $column = [$column];

        $column = $this->_normalizeColumns($column);

        $iColumns = new Columns($method, $column, $type);
        $iColumns->distinct($options['distinct'] ?? false);
        $iColumns->as($options['as'] ?? null);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function count($column=null, array $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('COUNT', $column ?? '*', $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return QueryBuilder
     */
    public function countDistinct(...$columns): QueryBuilder
    {// 2023-05-26
        $options = [];
        if(count($columns) > 1 && is_array(end($columns)) && is_string(key(end($columns)))) $options = array_pop($columns);

        if(count($columns) === 0) $columns = ['*'];

        $options['distinct'] = true;
        return $this->aggregate('COUNT', $columns, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function min($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('MIN', $column, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function max($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('MAX', $column, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function sum($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('SUM', $column, $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return QueryBuilder
     */
    public function sumDistinct(...$columns): QueryBuilder
    {// 2023-05-26
        $options = [];
        if(is_array(end($columns))) $options = array_pop($columns);

        if(count($columns) === 0) $columns = ['*'];

        $options['distinct'] = true;
        return $this->aggregate('SUM', $columns, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function avg($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('AVG', $column, $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return QueryBuilder
     */
    public function avgDistinct(...$columns): QueryBuilder
    {// 2023-05-26
        $options = [];
        if(is_array(end($columns))) $options = array_pop($columns);

        if(count($columns) === 0) $columns = ['*'];

        $options['distinct'] = true;
        return $this->aggregate('AVG', $columns, $options);
    }

    /**
     * see Union::TYPE_* constants
     * @param string $type Union::TYPE_* constant
     * @param array<int, callable|QueryBuilder> $args
     * @return QueryBuilder
     */
    private function _union(string $type, ...$args): QueryBuilder
    {// 2023-06-02
        $wrap = array_pop($args);

        if(!is_bool($wrap))
        {
            $args[] = $wrap;
            $wrap = false;
        }

        $callbacks = $args;

        if(is_array($args[0])) $callbacks = $args[0];

        foreach($callbacks as $callback)
        {
            $iUnion = new Union($type, $callback, $wrap ?? false);
            $this->iStatements[] = $iUnion;
        }

        return $this;
    }

    /**
     * @param array<int, callable|QueryBuilder> $args
     * @return QueryBuilder
     */
    public function union(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_BASIC, ...$args);
    }

    /**
     * @param array<int, callable|QueryBuilder> $args
     * @return QueryBuilder
     */
    public function unionAll(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_ALL, ...$args);
    }

    /**
     * @param array<int, callable|QueryBuilder> $args
     * @return QueryBuilder
     */
    public function intersect(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_INTERSECT, ...$args);
    }

    /**
     * @param int|Raw|QueryBuilder $value
     * @param array<int,mixed> $options
     * @return QueryBuilder
     */
    public function offset($value, ...$options): QueryBuilder
    {// 2023-05-26
        if($value === null || $value instanceof Raw || $value instanceof QueryBuilder)
        {
            $this->iSingle->offset = $value;
        }
        else
        {
            if(!is_integer($value)) throw new \InvalidArgumentException('Offset must be an integer.');
            if($value < 0) throw new \InvalidArgumentException('Offset must be greater than or equal to 0.');

            $this->iSingle->offset = $value;
        }

        // this._setSkipBinding('limit', options);

        return $this;
    }

    /**
     * @param int|Raw|QueryBuilder $value
     * @param array<int,mixed> $options
     * @return QueryBuilder
     */
    public function limit($value, ...$options): QueryBuilder
    {// 2023-05-26
        $this->iSingle->limit = $value;
        // this._setSkipBinding('limit', options);

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function clone(): QueryBuilder
    {// 2023-06-02j
        return clone $this;
    }

    /**
     * Sets method to DELETE
     * @return QueryBuilder
     */
    public function delete(): QueryBuilder
    {// 2023-06-02
        $this->method = self::METHOD_DELETE;

        return $this;
    }

    /**
     * see Group::TYPE_* constants
     * @param string $type Group::TYPE_* constant
     * @param string|Raw $column
     * @return QueryBuilder
     */
    private function _groupBy(string $type, $column): QueryBuilder
    {// 2023-06-05
        $iGroupBy = new Group($type, $column);

        $this->iStatements[] = $iGroupBy;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $columns
     * @return QueryBuilder
     */
    public function groupBy(...$columns): QueryBuilder
    {// 2023-06-05
        foreach($columns as $column)
        {
            if(!($column instanceof Raw)) $this->_groupBy(Group::TYPE_BASIC, $column);
            else $this->_groupBy(Group::TYPE_RAW, $column);
        }

        return $this;
    }

    /**
     * @param array<int, string> $columns
     * @return QueryBuilder
     */
    public function groupByRaw(...$columns): QueryBuilder
    {// 2023-06-05
        if(count($columns) === 0) throw new \InvalidArgumentException('groupByRaw() requires at least one argument.');

        foreach($columns as $column)
        {
            if(!($column instanceof Raw)) $this->_groupBy(Group::TYPE_RAW, new Raw($column));
            else $this->_groupBy(Group::TYPE_RAW, $column);
        }

        return $this;
    }

    /**
     * see Order::TYPE_* constants
     * see Order::DIRECTION_* constants
     * see Order::NULLS_POSITION_* constants
     *
     * @param string $type Order::TYPE_* constant
     * @param string|Raw $column
     * @param string|null $direction Order::DIRECTION_* constant
     * @param string|null $nullsPosition Order::NULLS_POSITION_* constants
     * @return QueryBuilder
     */
    private function _orderBy(string $type, $column, $direction=null, $nullsPosition=null): QueryBuilder
    {// 2023-06-05
        $iOrderBy = new Order($type, $column, $direction, $nullsPosition);

        $this->iStatements[] = $iOrderBy;

        return $this;
    }

    /**
     * see Order::DIRECTION_* constants
     * see Order::NULLS_POSITION_* constants
     *
     * @param string|Raw $column
     * @param string|null $direction Order::DIRECTION_* constant
     * @param string|null $nullsPosition Order::NULLS_POSITION_* constants
     * @return QueryBuilder
     */
    public function orderBy($column, $direction=Order::DIRECTION_ASC, $nullsPosition=null): QueryBuilder
    {// 2023-06-05
        if($column instanceof Raw) return $this->orderByRaw($column, $direction);

        if(is_string($column) || $column instanceof QueryBuilder) return $this->_orderBy(Order::TYPE_BASIC, $column, $direction, $nullsPosition);

        if(is_array($column))
        {
            foreach($column as $key=>$value)
            {
                if(is_string($key))
                {
                    $this->orderBy($key, $value, $nullsPosition);
                }
                else
                {
                    if(is_string($value)) $this->orderBy($value, $direction, $nullsPosition);
                    else $this->orderBy($value['column'], $value['order'] ?? Order::DIRECTION_ASC, $value['nulls'] ?? null);
                }
            }
        }

        return $this;
    }

    /**
     * see Order::DIRECTION_* constants
     *
     * @param string|Raw $column
     * @param string|null $direction Order::NULLS_POSITION_* constants
     *
     * @return QueryBuilder
     */
    public function orderByRaw($column, $direction=null): QueryBuilder
    {// 2023-06-05
        if(!($column instanceof Raw)) $column = new Raw($column);

        return $this->_orderBy(Order::TYPE_RAW, $column, $direction);
    }

    /**
     * see Having::TYPE_* constants
     * see QueryBuilder::BOOL_TYPE_* constants
     *
     * @param string $type Having::TYPE_* constant
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable $operator
     * @param string|Raw|QueryBuilder|callable $value
     * @param string $boolean QueryBuilder::BOOL_TYPE_* constant
     * @param bool $isNot
     * @return QueryBuilder
     */
    private function _having(string $type, $column, $operator, $value, $boolean, $isNot): QueryBuilder
    {// 2023-06-05
        $iHaving = new Having($type, $column, $operator, $value, $boolean, $isNot);

        $this->iStatements[] = $iHaving;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable|null $operator
     * @param string|Raw|QueryBuilder|callable|null $value
     * @return QueryBuilder
     */
    public function having($column, $operator=null, $value=null): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        if($column instanceof Raw) return $this->havingRaw($column);

        if(is_callable($column)) return $this->havingWrapped($column);

        return $this->_having(Having::TYPE_BASIC, $column, $operator, $value, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|QueryBuilder|callable|null $operator
     * @param string|Raw|QueryBuilder|callable|null $value
     * @return QueryBuilder
     */
    public function orHaving($column, $operator=null, $value=null): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        if($column instanceof Raw) return $this->orHavingRaw($column);

        if(is_callable($column)) return $this->orHavingWrapped($column);

        return $this->_having(Having::TYPE_BASIC, $column, $operator, $value, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function havingNull($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orHavingNull($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function havingNotNull($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = true;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orHavingNotNull($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = true;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function havingExists($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orHavingExists($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function havingNotExists($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = true;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orHavingNotExists($column): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = true;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder> $values
     * @return QueryBuilder
     */
    private function _havingBetween($column, array $values): QueryBuilder
    {// 2023-06-05
        if(count($values) !== 2) throw new \InvalidArgumentException('You must specify 2 values for the havingBetween clause');

        return $this->_having(Having::TYPE_BETWEEN, $column, null, $values, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function havingBetween($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @return QueryBuilder
     */
    public function orHavingBetween($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder> $values
     * @return QueryBuilder
     */
    public function havingNotBetween($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = true;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|QueryBuilder> $values
     * @return QueryBuilder
     */
    public function orHavingNotBetween($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = true;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $value
     * @return QueryBuilder
     */
    public function havingRaw($value): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        $iRaw = $value;

        if(!($value instanceof Raw)) $iRaw = new Raw($value);

        return $this->_having(Having::TYPE_RAW, null, null, $iRaw, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $value
     * @return QueryBuilder
     */
    public function orHavingRaw($value): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        $iRaw = $value;

        if(!($value instanceof Raw)) $iRaw = new Raw($value);

        return $this->_having(Having::TYPE_RAW, null, null, $iRaw, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return QueryBuilder
     */
    private function _havingIn($column, array $values): QueryBuilder
    {// 2023-06-05
        if(count($values) === 0) return $this;

        return $this->_having(Having::TYPE_IN, $column, null, $values, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return QueryBuilder
     */
    public function havingIn($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return QueryBuilder
     */
    public function orHavingIn($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return QueryBuilder
     */
    public function havingNotIn($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = true;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return QueryBuilder
     */
    public function orHavingNotIn($column, array $values): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = true;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function havingWrapped(callable $callback): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot = false;

        return $this->_having(Having::TYPE_WRAPPED, null, null, $callback, $this->boolType, $this->isNot);
    }

    /**
     * @param callable $callback
     * @return QueryBuilder
     */
    public function orHavingWrapped(callable $callback): QueryBuilder
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot = false;

        return $this->_having(Having::TYPE_WRAPPED, null, null, $callback, $this->boolType, $this->isNot);
    }

    /**
     * Sets the values for an `update`, allowing for both
     * Support for `.update(key, value, [returning])` and `.update(obj, [returning])` syntaxes.
     *
     * @param array<int, string|Raw|QueryBuilder>|string $args [values, returning, options]
     * @return QueryBuilder
     */
    public function update(...$args): QueryBuilder
    {// 2023-06-05
        $values = $args[0] ?? null;
        $returning = $args[1] ?? null;
        $options = $args[2] ?? null;

        $this->method = self::METHOD_UPDATE;

        $data = [];
        $ret = null;
        if(is_string($values))
        {
            $data[$values] = $returning;

            if(func_num_args() > 2) $ret = func_get_arg(2);
        }
        else
        {
            foreach($values as $key=>$value)
            {
                $data[$key] = $value;
            }

            if(func_num_args() > 1) $ret = func_get_arg(1);
        }

        if($ret) $this->returning($ret, $options);

        $this->iSingle->update = $data;

        return $this;
    }

    /**
     * @param array<int, string|Raw|QueryBuilder> $args [values, returning, options]
     * @return QueryBuilder
     */
    public function insert(...$args): QueryBuilder
    {// 2023-06-06
        $values = $args[0] ?? null;
        $returning = $args[1] ?? null;
        $options = $args[2] ?? null;

        if(func_num_args() === 0) throw new \InvalidArgumentException('insert() must be called with at least one argument');
        if(is_array($values) && count($values) === 0) throw new \InvalidArgumentException('insert() must be called with at least one argument');

        $this->method = self::METHOD_INSERT;

        if($returning) $this->returning($returning, $options);

        $this->iSingle->insert = $values;

        return $this;
    }

    /**
     * @param string|array<int, string|Raw> $columns
     * @return OnConflictBuilder
     */
    public function onConflict($columns): OnConflictBuilder
    {// 2023-06-06
        if(is_string($columns)) $columns = [$columns];

        return new OnConflictBuilder($this, $columns);
    }

    /**
     * @param string|Raw|array<int, string|Raw> $returning
     * @param array<string, mixed> $options
     * @return QueryBuilder
     */
    public function returning($returning, $options): QueryBuilder
    {// 2023-06-05
        $this->iSingle->returning = $returning;
        $this->iSingle->options = $options;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param int|float $amount
     * @return QueryBuilder
     */
    private function _counter($column, $amount): QueryBuilder
    {// 2023-06-05
        $this->method = self::METHOD_UPDATE;

        $this->iSingle->counter = $this->iSingle->counter ?? [];

        $this->iSingle->counter[$column] = $amount;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param int|float $amount
     * @return QueryBuilder
     */
    public function increment($column, $amount=null): QueryBuilder
    {// 2023-06-05
        if(is_array($column))
        {
            foreach($column as $key=>$value)
            {
                $this->_counter($key, $value);
            }

            return $this;
        }

        return $this->_counter($column, $amount);
    }

    /**
     * @param string|Raw $column
     * @param int|float $amount
     * @return QueryBuilder
     */
    public function decrement($column, $amount=null): QueryBuilder
    {// 2023-06-05
        if(is_array($column))
        {
            foreach($column as $key=>$value)
            {
                $this->_counter($key, -$value);
            }

            return $this;
        }

        return $this->_counter($column, -$amount);
    }

    /**
     * @param string|Raw $table
     * @return QueryBuilder
     */
    public function truncate($table=null): QueryBuilder
    {// 2023-06-06
        $this->method = self::METHOD_TRUNCATE;

        if($table) return $this->table($table);

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return QueryBuilder
     */
    public function forUpdate(...$tables): QueryBuilder
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_UPDATE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return QueryBuilder
     */
    public function forShare(...$tables): QueryBuilder
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_SHARE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return QueryBuilder
     */
    public function forNoKeyUpdate(...$tables): QueryBuilder
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_NO_KEY_UPDATE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return QueryBuilder
     */
    public function forKeyShare(...$tables): QueryBuilder
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_KEY_SHARE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @internal Helper method
     * @return bool
     */
    private function isSelectQuery(): bool
    {// 2023-06-07
        return in_array($this->getMethod(), [self::METHOD_SELECT, self::METHOD_FIRST, self::METHOD_PLUCK]);
    }

    /**
     * Skips locked rows when using a lock constraint.
     * @return QueryBuilder
     */
    public function skipLocked(): QueryBuilder
    {// 2023-06-07
        if(!$this->isSelectQuery()) throw new \LogicException("Cannot chain ->skipLocked() on \"{$this->getMethod()}\" query!");

        if($this->iSingle->lock === null) throw new \LogicException('->skipLocked() can only be used after a call to ->forShare() or ->forUpdate()!');

        if($this->iSingle->waitMode === self::WAIT_MODE_NO_WAIT) throw new \LogicException('->skipLocked() cannot be used together with ->noWait()!');

        $this->iSingle->waitMode = self::WAIT_MODE_SKIP_LOCKED;

        return $this;
    }

    /**
     * Causes error when acessing a locked row instead of waiting for it to be released.
     * @return QueryBuilder
     */
    public function noWait(): QueryBuilder
    {// 2023-06-07
        if(!$this->isSelectQuery()) throw new \LogicException("Cannot chain ->noWait() on \"{$this->getMethod()}\" query!");

        if($this->iSingle->lock=== null) throw new \LogicException('->noWait() can only be used after a call to ->forShare() or ->forUpdate()!');

        if($this->iSingle->waitMode === self::WAIT_MODE_SKIP_LOCKED) throw new \LogicException('->noWait() cannot be used together with ->skipLocked()!');

        $this->iSingle->waitMode = self::WAIT_MODE_NO_WAIT;

        return $this;
    }

    /**
     * @param callable $callback
     * @param array<int, mixed> $args
     */
    public function modify(callable $callback, ...$args): QueryBuilder
    {// 2023-06-07
        $callback($this, ...$args);

        return $this;
    }

    public function transaction(?Transaction $iTransaction=null): QueryBuilder
    {// 2023-06-07
        $this->iSingle->transaction = $iTransaction;

        return $this;
    }

    /**
     * @return array<int, mixed>|mixed
     * @throws \PDOException
     */
    public function run()
    {// 2023-06-12
        $iQueryCompiler = new QueryCompiler($this->iClient, $this, []);

        $iQuery = $iQueryCompiler->toSQL();

        $statement = $this->iClient->query($iQuery);

        $result = ($this->getSelectMethod() === self::METHOD_FIRST)
            ? $statement->fetch()
            : $statement->fetchAll();

        $statement->closeCursor();

        if($result === false) return null;

        return $result;
    }

    public function toSQL(): Query
    {// 2023-06-12
        $iQueryCompiler = new QueryCompiler($this->iClient, $this, []);

        return $iQueryCompiler->toSQL();
    }
}

