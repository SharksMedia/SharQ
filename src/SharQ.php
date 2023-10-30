<?php
/**
 * Class SharQ
 * 2023-05-08
 *
 * @author      Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

use Closure;
use Sharksmedia\SharQ\Client;
use Sharksmedia\SharQ\Single;
use Sharksmedia\SharQ\OnConflictBuilder;

use Sharksmedia\SharQ\Statement\IStatement;

use Sharksmedia\SharQ\Statement\Comments;
use Sharksmedia\SharQ\Statement\With;
use Sharksmedia\SharQ\Statement\Columns;
use Sharksmedia\SharQ\Statement\HintComments;
use Sharksmedia\SharQ\Statement\Join;
use Sharksmedia\SharQ\Statement\Where;
use Sharksmedia\SharQ\Statement\Having;
use Sharksmedia\SharQ\Statement\Group;
use Sharksmedia\SharQ\Statement\Order;
use Sharksmedia\SharQ\Statement\Union;
use Sharksmedia\SharQ\Statement\Raw;
use SharQResultGenerator;

class SharQ
{
    public const BOOL_TYPE_AND = 'AND';
    public const BOOL_TYPE_OR  = 'OR';
    
    public const METHOD_RAW      = 'RAW';
    public const METHOD_SELECT   = 'SELECT';
    public const METHOD_FIRST    = 'FIRST';
    public const METHOD_PLUCK    = 'PLUCK';
    public const METHOD_INSERT   = 'INSERT';
    public const METHOD_UPDATE   = 'UPDATE';
    public const METHOD_DELETE   = 'DELETE';
    public const METHOD_TRUNCATE = 'TRUNCATE';

    public const LOCK_MODE_FOR_UPDATE        = 'FOR UPDATE';
    public const LOCK_MODE_FOR_SHARE         = 'FOR SHARE';
    public const LOCK_MODE_FOR_NO_KEY_UPDATE = 'FOR NO KEY UPDATE';
    public const LOCK_MODE_FOR_KEY_SHARE     = 'FOR KEY SHARE';

    public const WAIT_MODE_SKIP_LOCKED = 'SKIP LOCKED';
    public const WAIT_MODE_NO_WAIT     = 'NO WAIT';

    /**
     * returns an array indexed by column name as returned in your result set.
     */
    public const FETCH_MODE_ASSOCIATIVE = \PDO::FETCH_ASSOC;

    /**
     * returns an anonymous object with property names that correspond to the column names returned in your result set.
     */
    public const FETCH_MODE_OBJECT = \PDO::FETCH_OBJ;

    /**
     * returns an array indexed by column number as returned in your result set, starting at column 0.
     */
    public const FETCH_MODE_NUMBERED = \PDO::FETCH_NUM;

    /**
     * returns an array where each key is the first column's value and each value is the second column's value as returned in your result set.
     */
    public const FETCH_MODE_KEY_PAIR = \PDO::FETCH_KEY_PAIR;

    /**
     * returns an array where each value corresponds to a single column's value as returned in your result set, defaults to column 0
     */
    public const FETCH_MODE_COLUMN = \PDO::FETCH_COLUMN;

    public const FETCH_METHOD_ALL       = 'FETCH_METHOD_ALL';
    public const FETCH_METHOD_GENERATOR = 'FETCH_METHOD_GENERATOR';

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
     * This is the fetchMode attribute.
     * see SharQ::FETCH_MODE_* constants
     * @var int
     */
    protected int $fetchMode = self::FETCH_MODE_ASSOCIATIVE;

    protected string $fetchMethod = self::FETCH_METHOD_ALL;

    /**
     * This is the method attribute.
     * see SharQ::METHOD_* constants
     * @var string
     */
    protected string $method = self::METHOD_SELECT;

    /**
     * This is the iSingle attribute.
     * @var Single
     */
    protected Single $iSingle;

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
     * see SharQ::BOOL_TYPE_* constants
     * @var string
     */
    private string $boolType = self::BOOL_TYPE_AND;

    /**
     * This is the isNot attribute.
     * @var bool
     */
    private bool   $isNot = false;

    public function __construct(Client $iClient, ?string $schema = null)
    {// 2023-05-08
        $this->iClient = $iClient;
        $this->schema  = $schema ?? $iClient->getConfig()->getDatabase();
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
     * @deprecated
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
     * see SharQ::METHOD_* constants
     * @return string Returns the method used.
     */
    public function getMethod(): string
    {// 2023-06-05
        return $this->method;
    }

    /**
     * Clears a specific grouping.
     * @param string $type
     * @return SharQ
     */
    private function clearGrouping(string $type): self
    {// 2023-05-15
        $this->iStatements = array_filter($this->iStatements, function($statement) use ($type)
        {
            return !($statement instanceof $type);
        });

        return $this;
    }

    /**
     * Clears ->with statements
     * @return SharQ
     */
    public function clearWith(): self
    {// 2023-05-15
        return $this->clearGrouping(With::class);
    }

    /**
     * Clears ->select statements
     * @return SharQ
     */
    public function clearSelect(): self
    {// 2023-05-15
        return $this->clearGrouping(Columns::class);
    }

    /**
     * Clears ->join statements
     * @return SharQ
     */
    public function clearJoin(): self
    {// 2023-05-15
        return $this->clearGrouping(Join::class);
    }

    /**
     * Clears ->union statements
     * @return SharQ
     */
    public function clearUnion(): self
    {// 2023-05-15
        return $this->clearGrouping(Union::class);
    }

    /**
     * Clears ->hintComment statements
     * @return SharQ
     */
    public function clearHintComments(): self
    {// 2023-05-15
        return $this->clearGrouping(HintComments::class);
    }

    /**
     * Clears ->where statements
     * @return SharQ
     */
    public function clearWhere(): self
    {// 2023-06-01
        return $this->clearGrouping(Where::class);
    }

    /**
     * Clears ->increment, ->decrement statements
     * @return SharQ
     */
    public function clearCounters(): self
    {// 2023-06-01
        $this->iSingle->counter = null;

        return $this;
    }

    /**
     * Clears ->groupBy statements
     * @return SharQ
     */
    public function clearGroup(): self
    {// 2023-06-01
        return $this->clearGrouping(Group::class);
    }

    /**
     * Clears ->orderBy statements
     * @return SharQ
     */
    public function clearOrder(): self
    {// 2023-06-01
        return $this->clearGrouping(Order::class);
    }

    /**
     * Clears ->having statements
     * @return SharQ
     */
    public function clearHaving(): self
    {// 2023-06-01
        return $this->clearGrouping(Having::class);
    }

    /**
     * Clears ->limit statements
     * @return SharQ
     */
    public function clearLimit(): self
    {// 2023-06-01
        $this->iSingle->limit = null;

        return $this;
    }

    /**
     * Clears ->offset statements
     * @return SharQ
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
     * @return SharQ
     */
    public function clear(string $statementName): self
    {// 2023-06-07
        $statementMap =
        [
            'with'         => 'clearWith',
            'select'       => 'clearSelect',
            'columns'      => 'clearSelect',
            'hintComments' => 'clearHintComments',
            'where'        => 'clearWhere',
            'union'        => 'clearUnion',
            'join'         => 'clearJoin',
            'group'        => 'clearGroup',
            'order'        => 'clearOrder',
            'having'       => 'clearHaving',
            'limit'        => 'clearLimit',
            'offset'       => 'clearOffset',
            'counter'      => 'clearCounters',
            'counters'     => 'clearCounters'
        ];

        $statementClearingFunction = $statementMap[$statementName] ?? null;

        if ($statementClearingFunction === null)
        {
            throw new \Exception('Unknown statement: '.$statementName);
        }

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
     * @param string|Raw|SharQ|\Closure $arg
     * @return bool
     */
    private function isValidStatementArg($arg): bool
    {// 2023-06-07
        return $arg instanceof \Closure || $arg instanceof Raw || $arg instanceof SharQ;
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement, method]
     */
    private function validateWithArgs(string $alias, ...$args): void
    {// 2023-06-07
        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement    = $args[1] ?? null;
        $method                = $args[2] ?? null;

        if (func_num_args() === 2)
        {
            if ($this->isValidStatementArg($statementOrColumnList))
            {
                return;
            }
        }

        $isNonEmptyList = is_array($statementOrColumnList)
                          &&
                          count($statementOrColumnList) !== 0
                          &&
                          array_reduce($statementOrColumnList, fn($carry, $item) => $carry && is_string($item), true);

        if (!$isNonEmptyList)
        {
            throw new \Exception('Invalid with statement arguments');
        }

        if ($this->isValidStatementArg($nothingOrStatement))
        {
            return;
        }

        throw new \Exception("{$method}() third argument must be a function / SharQ or a raw when its second argument is a column name list");
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement, method]
     */
    public function with(string $alias, ...$args): SharQ
    {// 2023-05-15
        $this->validateWithArgs($alias, ...$args);

        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement    = $args[1] ?? null;
        // $method = $args[2] ?? null;

        return $this->withWrapped($alias, $statementOrColumnList, $nothingOrStatement);
    }
    /**
     * @param mixed $args
     */
    public function withRecursive(string $alias, ...$args): SharQ
    {// 2023-07-31
        return $this->withRecursiveWrapped($alias, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function withRecursiveWrapped(string $alias, ...$args): SharQ
    {// 2023-07-31
        $this->validateWithArgs($alias, ...$args);

        return $this->_withWrapped(With::TYPE_RECURSIVE_WRAPPED, $alias, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function withMaterialized(string $alias, ...$args): SharQ
    {// 2023-07-31
        return $this->withMaterializedWrapped($alias, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function withMaterializedWrapped(string $alias, ...$args): SharQ
    {
        $this->validateWithArgs($alias, ...$args);

        return $this->_withWrapped(With::TYPE_MATERIALIZED_WRAPPED, $alias, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function withNotMaterialized(string $alias, ...$args): SharQ
    {// 2023-07-31
        return $this->withNotMaterializedWrapped($alias, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function withNotMaterializedWrapped(string $alias, ...$args): SharQ
    {
        $this->validateWithArgs($alias, ...$args);

        return $this->_withWrapped(With::TYPE_NOT_MATERIALIZED_WRAPPED, $alias, ...$args);
    }

    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement]
     */
    private function _withWrapped(string $type, string $alias, ...$args): SharQ
    {// 2023-05-15
        $statementOrColumnList = $args[0] ?? null;
        $nothingOrStatement    = $args[1] ?? null;

        $statement  = null;
        $columnList = null;

        if ($this->isValidStatementArg($statementOrColumnList))
        {
            $statement = $statementOrColumnList;
        }
        else
        {
            $columnList = $statementOrColumnList;
            $statement  = $nothingOrStatement;
        }

        $iWith = new With($type, $alias, $columnList, $statement);

        $this->iStatements[] = $iWith;

        return $this;
    }
    /**
     * @param string $alias
     * @param array<int, mixed> $args [statementOrColumnList, nothingOrStatement]
     */
    public function withWrapped(string $alias, ...$args): SharQ
    {// 2023-07-31
        return $this->_withWrapped(With::TYPE_WRAPPED, $alias, ...$args);
    }

    /**
     * @param string|Raw|SharQ|array<int, string|Raw|SharQ> $tableName
     */
    public function table($tableName): SharQ
    {// 2023-05-15
        if (!is_array($tableName))
        {
            $tableName = [$tableName];
        }

        $tableName = $this->_normalizeColumns([$tableName]);

        $this->iSingle->table = $tableName;

        return $this;
    }

    /**
     * @param string|Raw|SharQ|array<int, string|Raw|SharQ> $tableName
     */
    public function from($tableName): SharQ
    {// 2023-05-15
        return $this->table($tableName);
    }

    /**
     * @param string $raw
     */
    public function fromRaw(string $raw): SharQ
    {// 2023-05-15
        return $this->table(new Raw($raw));
    }

    /**
     * @param string|Raw|SharQ|array<int, string|Raw|SharQ> $tableName
     */
    public function into($tableName): SharQ
    {// 2023-05-15
        return $this->table($tableName);
    }

    /**
     * @param string|Raw|null $schemaName
     */
    public function withSchema($schemaName): SharQ
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
    public function timeout(int $milliSeconds, bool $cancel = false): SharQ
    {// 2023-05-08
        // 2023-05-08 TODO: implement me
        if ($milliSeconds < 0)
        {
            throw new \UnexpectedValueException('Timeout must be a positive integer');
        }

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
     * @param array<int|string, string|Raw|SharQ> $columns One or more values
     * @return SharQ
     */
    public function column(...$columns): SharQ
    {// 2023-05-08
        return $this->_column($columns, Columns::TYPE_PLUCK);
    }

    /**
     * @param array<int,mixed> $columns
     * @return array<int,mixed>|mixed|bool
     */
    private function _normalizeColumns(array $columns): array
    {
        if (count($columns) === 1)
        {
            $col = reset($columns);

            if (is_array($col) && is_integer(key($col)))
            {
                $columns = $col;
            }
        }
        
        foreach ($columns as $i => $column)
        {
            if (is_string($column) && is_integer($i))
            {
                // 2023-05-30 Handling cases where column is aliased in a string ie. ' foo  aS bar'
                preg_match('/(\\S+)\\s+as\\s+(\\S+)/i', trim($column), $matches);
                
                if (count($matches) === 3)
                {
                    $columns[$i] = [$matches[2] => $matches[1]];
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
    private function _column(array $columns, string $type): SharQ
    {// 2023-05-15
        $columns = $this->_normalizeColumns($columns);
        
        $iColumns = new Columns(null, $columns, $type);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * 2023-05-08
     * @param array<int, string|Raw|SharQ> $columns One or more values
     * @return SharQ
     */
    public function distinct(string ...$columns): SharQ
    {// 2023-05-08
        $normalizedColumns = $this->_normalizeColumns([]);

        $iColumns = new Columns(null, $normalizedColumns);
        $iColumns->distinct(true);

        $this->iStatements[] = $iColumns;

        return $this->_column($columns, COLUMNS::TYPE_PLUCK);
    }

    /**
     * 2023-05-08
     * @param array<int, string|Raw|SharQ> $columns One or more values
     * @return SharQ
     */
    public function distinctOn(string ...$columns): SharQ
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
     * @return SharQ
     */
    public function as(string $alias): SharQ
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
     * @return SharQ
     */
    public function hintComment(...$hintComments): SharQ
    {// 2023-05-09
        $iHintComments = new HintComments($hintComments);

        $this->iStatements[] = $iHintComments;

        return $this;
    }

    /**
     * 2023-05-08
     * @param string[] $comments One or more values
     * @return SharQ
     */
    public function comment(...$comments): SharQ
    {// 2023-05-09
        $iComments = new Comments($comments);

        $this->iStatements[] = $iComments;

        return $this;
    }

    /**
     * Sets the values for a `select` query
     * @param array<int, string|Raw|SharQ> $columns One or more values
     * @return SharQ
     */
    public function select(...$columns): SharQ
    {// 2023-05-15
        $this->iSingle->columnMethod = Columns::TYPE_PLUCK;

        return $this->_column($columns, Columns::TYPE_PLUCK);
    }

    /**
     * Sets the values for a `select` query, informing that only the first row should be returned (limit 1).
     * @param array<int, string|Raw|SharQ> $columns One or more values
     * @return SharQ
     */
    public function first(...$columns): SharQ
    {// 2023-05-15
        $this->method = self::METHOD_FIRST;

        if (count($columns) !== 0)
        {
            $this->_column($columns, Columns::TYPE_PLUCK);
        }

        return $this->limit(1);
    }
    /**
     * @param mixed $table
     * @param mixed $first
     * @param mixed $args
     */
    private function _join(string $joinFlag, $table, $first = null, ...$args): SharQ
    {// 2023-08-18
        $iJoin = null;

        $tableParts = is_string($table)
            ? preg_split('/ AS /i', $table)
            : [$table];

        $table = array_shift($tableParts);
        $alias = array_shift($tableParts);
        
        if ($first instanceof \Closure)
        {
            $iJoin = new Join($table, $joinFlag, $this->schema);
            $first($iJoin);
        }
        else if ($joinFlag === Join::TYPE_RAW)
        {
            $iJoin = new Join(new Raw($table, $first), Join::TYPE_RAW);
        }
        // else if($this->joinFlag === Join::TYPE_CROSS)
        // {
        //     $iJoin = new Join($table, Join::TYPE_CROSS, $this->schema);
        // }
        else
        {
            $iJoin = new Join($table, $joinFlag, $this->schema);

            if ($first)
            {
                $iJoin->on($first, ...$args);
            }
        }

        if ($alias)
        {
            $iJoin->as($alias);
        }

        $this->iStatements[] = $iJoin;

        return $this;
    }

    /**
     * 2023-05-08
     *
     * join(string $table, string $first, string $operator, string $second)
     * join(string $table, \Closure $first)
     *
     * @param string|Raw $table
     * @param string|Raw|SharQ|\Closure $first on statement [column]
     * @param array<int,string|Raw|SharQ> $args on statement [operator, value]
     * @return SharQ
     */
    public function join($table, $first = null, ...$args): SharQ
    {
        return $this->_join(Join::TYPE_INNER, $table, $first, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function innerJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_INNER, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function leftJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_LEFT, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function leftOuterJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_LEFT_OUTER, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function rightJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_RIGHT, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function rightOuterJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_RIGHT_OUTER, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function outerJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_OUTER, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function fullOuterJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_FULL_OUTER, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function crossJoin(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_CROSS, ...$args);
    }

    /**
     * @param array<int,string|Raw|SharQ> $args on statement [column, operator, value]
     * @return SharQ
     */
    public function joinRaw(...$args): SharQ
    {// 2023-05-09
        return $this->_join(Join::TYPE_RAW, ...$args);
    }

    /**
     * Where modifier. Changes bool type to OR
     * @return SharQ
     */
    public function or(): self
    {// 2023-05-09
        $this->boolType = self::BOOL_TYPE_OR;

        return $this;
    }

    /**
     * Where modifier. Changes not type
     * @return SharQ
     */
    public function not(): self
    {// 2023-05-09
        $this->isNot = true;

        return $this;
    }
    /**
     * @param mixed $args
     */
    public function _where(?string $whereFlag, ?string $boolType, ?bool $isNot, ...$args): SharQ
    {
        $boolType ??= $this->boolType;
        $isNot    ??= $this->isNot;

        $column   = $args[0] ?? null;
        $operator = $args[1] ?? null;
        $value    = $args[2] ?? null;

        $argCount = func_num_args() - 3;

        // Check if the column is a function, in which case it's
        // a where statement wrapped in parens.
        if ($column instanceof \Closure)
        {
            return $this->_whereWrapped($boolType, $isNot, $column);
        }

        // Check if the column is an array, in which case it's multiple wheres
        if (is_array($column))
        {
            if (count($column) === 1)
            {
                $col   = key($column);
                $value = reset($column);

                // If the first value in the array is an integer, we will assume that
                // the developer wants to run a where-in statement with a basic SQL
                // clause. The values in the array will be the only values placed
                // in the "IN" clause, while the columns are specified in SQL.
                return $this->_where($whereFlag, $boolType, $isNot, $col, '=', $value);
            }

            if ($boolType === self::BOOL_TYPE_AND)
            {
                foreach ($column as $columnName => $value)
                {
                    // $this->andWhere($columnName, '=', $value);
                    $this->_andWhere($isNot, $columnName, '=', $value);
                }

                return $this;
            }
            
            $this->_whereWrapped($boolType, $isNot, function($q) use ($column, $isNot)
            {
                foreach ($column as $columnName => $value)
                {
                    // $this->andWhere($columnName, '=', $value);
                    $q->_andWhere($isNot, $columnName, '=', $value);
                }
            });

            return $this;
        }

        if ($argCount === 1)
        {
            // Allow a raw statement to be passed along to the query.
            if ($column instanceof Raw)
            {
                return $this->_whereRaw($boolType, $isNot, $column->getSQL(), ...$column->getBindings());
            }

            // Support "where true || where false"
            if (is_bool($column))
            {
                return $this->_whereRaw($boolType, $isNot, ($column ? '1' : '0').' = 1');
            }
        }

        if ($argCount === 2)
        {
            // Push onto the where statement stack.
            // operator is assumed to be value
            // $iWhere = new Where($column, null, $operator, $this->boolType, $this->isNot, Where::TYPE_BASIC);

            if (is_null($operator))
            {
                return $this->_whereNull($boolType, $isNot, $column);
            }

            if (is_bool($operator))
            {
                $operator = (int)$operator;
            }

            $iWhere = new Where($column, null, $operator, $boolType ?? $this->boolType, $isNot ?? $this->isNot, $whereFlag ?? Where::TYPE_BASIC);

            $this->iStatements[] = $iWhere;

            return $this;
        }

        if ($argCount === 3)
        {
            // lower case the operator for comparison purposes
            $checkOperator = strtolower(trim($operator ?? ''));

            if (in_array($checkOperator, ['in', 'not in']))
            {
                $isNot = $checkOperator === 'not in';

                return $this->_whereIn($boolType, $isNot, $column, $value);
            }

            if (in_array($checkOperator, ['between', 'not between']))
            {
                $isNot = $checkOperator === 'not between';

                return $this->_whereBetween($boolType, $isNot, $column, $value);
            }
        }

        // If the value is still null, check whether they're meaning, where value is null
        if ($value === null)
        {
            if (in_array($operator, ['is', 'is not', '=', '!=']))
            {
                $isNot = $checkOperator === 'is not' || $checkOperator === '!=';

                return $this->_whereNull($boolType, $isNot, $column);
            }
        }

        $whereFlag ??= Where::TYPE_BASIC;

        // Push onto the where statement stack.
        $iWhere = new Where($column, $operator, $value, $boolType ?? $this->boolType, $isNot ?? $this->isNot, $whereFlag);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw|SharQ|\Closure $args [column, operator, value]
     * @return SharQ
     */
    public function where(...$args): SharQ
    {// 2023-05-09
        return $this->_where(null, null, null, ...$args);
    
    //     $column = $args[0] ?? null;
    //     $operator = $args[1] ?? null;
    //     $value = $args[2] ?? null;
    //
    //     $argCount = func_num_args();
    //
    //     // $args = array_filter(func_get_args(), fn($value) => !is_null($value));
    //     // $argCount = count($args);
    //
    //     // Check if the column is a function, in which case it's
    //     // a where statement wrapped in parens.
    //     if($column instanceof \Closure) return $this->whereWrapped($column);
    //
    //     // Check if the column is an array, in which case it's multiple wheres
    //     if(is_array($column))
    //     {
    //         if(count($column) === 1)
    //         {
    //             $col = key($column);
    //             $value = reset($column);
    //             // If the first value in the array is an integer, we will assume that
    //             // the developer wants to run a where-in statement with a basic SQL
    //             // clause. The values in the array will be the only values placed
    //             // in the "IN" clause, while the columns are specified in SQL.
    //             return $this->where($col, '=', $value);
    //         }
    //
    //         if($this->boolType === self::BOOL_TYPE_AND)
    //         {
    //             foreach($column as $columnName=>$value)
    //             {
    //                 // $this->andWhere($columnName, '=', $value);
    //                 $this->andWhere($columnName, '=', $value);
    //             }
    //
    //             return $this;
    //         }
    //         
    //         $this->where(function($q) use($column)
    //         {
    //             foreach($column as $columnName=>$value)
    //             {
    //                 // $this->andWhere($columnName, '=', $value);
    //                 $q->andWhere($columnName, '=', $value);
    //             }
    //         });
    //
    //         return $this;
    //     }
    //
    //     if($argCount === 1)
    //     {
    //         // Allow a raw statement to be passed along to the query.
    //         if($column instanceof Raw) return $this->whereRaw($column->getSQL(), ...$column->getBindings());
    //
    //         // Support "where true || where false"
    //         if(is_bool($column)) return $this->whereRaw(($column ? '1' : '0').' = 1');
    //     }
    //
    //     if($argCount === 2)
    //     {
    //         // Push onto the where statement stack.
    //         // operator is assumed to be value
    //         // $iWhere = new Where($column, null, $operator, $this->boolType, $this->isNot, Where::TYPE_BASIC);
    //
    //         if(is_null($operator)) return $this->whereNull($column);
    //         if(is_bool($operator)) $operator = (int)$operator;
    //
    //         $iWhere = new Where($column, null, $operator, $this->boolType, $this->isNot, $this->whereFlag ?? Where::TYPE_BASIC);
    //
    //         $this->iStatements[] = $iWhere;
    //
    //         return $this;
    //     }
    //
    //     if($argCount === 3)
    //     {
    //         // lower case the operator for comparison purposes
    //         $checkOperator = strtolower(trim($operator ?? ''));
    //
    //         if(in_array($checkOperator, ['in', 'not in']))
    //         {
    //             $this->isNot = $checkOperator === 'not in';
    //             return $this->whereIn($column, $value);
    //         }
    //
    //         if(in_array($checkOperator, ['between', 'not between']))
    //         {
    //             $this->isNot = $checkOperator === 'not between';
    //             return $this->_whereBetween($column, $value);
    //         }
    //     }
    //
    //     // If the value is still null, check whether they're meaning, where value is null
    //     if($value === null)
    //     {
    //         if(in_array($operator, ['is', 'is not', '=', '!=']))
    //         {
    //             $this->isNot = $checkOperator === 'is not' || $checkOperator === '!=';
    //             return $this->whereNull($column);
    //         }
    //     }
    //
    //     // Push onto the where statement stack.
    //     $iWhere = new Where($column, $operator, $value, $this->boolType, $this->isNot, Where::TYPE_BASIC);
    //
    //     $this->iStatements[] = $iWhere;
    //
    //     return $this;
    }

    /**
     * @param string|Raw|SharQ|\Closure $args [column, operator, value]
     * @return SharQ
     */
    public function whereColumn(...$args): SharQ
    {// 2023-06-01
        // $this->whereFlag = Where::TYPE_COLUMN;
        // return $this->where(...$args);

        return $this->_where(Where::TYPE_COLUMN, null, null, ...$args);
    }
    /**
     * @param mixed $args
     */
    private function _andWhere(?bool $isNot, ...$args)
    {
        return $this->_where(Where::TYPE_BASIC, self::BOOL_TYPE_AND, $isNot, ...$args);
    }

    /**
     * @param array<int, string|Raw|SharQ|\Closure> $args [column, operator, value]
     * @return SharQ
     */
    public function andWhere(...$args): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_BASIC;
        // $this->boolType = self::BOOL_TYPE_AND;
        // return $this->where(...$args);

        return $this->_andWhere(null, ...$args);
    }
    /**
     * @param mixed $args
     */
    private function _orWhere(?bool $isNot, ...$args)
    {
        return $this->_where(Where::TYPE_BASIC, self::BOOL_TYPE_OR, $isNot, ...$args);
    }

    /**
     * @param array<int, string|Raw|SharQ|\Closure> $args [column, operator, value]
     * @return SharQ
     */
    public function orWhere(...$args): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_BASIC;
        // $this->boolType = self::BOOL_TYPE_OR;
        // return $this->where(...$args);

        return $this->_orWhere(null, ...$args);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $args [column, operator, value]
     * @return SharQ
     */
    public function whereNot(...$args): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_BASIC;
        // $this->isNot = true;
        // return $this->where(...$args);

        return $this->_where(Where::TYPE_BASIC, null, true, ...$args);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $args [column, operator, value]
     * @return SharQ
     */
    public function orWhereNot(...$args): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_BASIC;
        // $this->boolType = self::BOOL_TYPE_OR;
        // $this->isNot = true;
        // return $this->where(...$args);

        return $this->_where(Where::TYPE_BASIC, self::BOOL_TYPE_OR, true, ...$args);
    }
    /**
     * @param mixed $bindings
     */
    private function _whereRaw(?string $boolType, ?bool $isNot, string $sql, ...$bindings): SharQ
    {
        $iRaw   = new Raw($sql, ...$bindings);
        $iWhere = new Where(null, null, $iRaw, $boolType ?? $this->boolType, $isNot ?? $this->isNot, Where::TYPE_RAW);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param array<int,mixed> $bindings
     * @return SharQ
     */
    public function whereRaw(string $sql, ...$bindings): SharQ
    {// 2023-05-09
        return $this->_whereRaw(null, null, $sql, ...$bindings);
    }
    /**
     * @param Closure(): void $callback
     */
    public function _whereWrapped(?string $boolType, ?bool $isNot, \Closure $callback): SharQ
    {
        $iSharQ = new SharQ($this->iClient, $this->schema);
        $callback($iSharQ);

        $iWhere = new Where(null, null, $callback, $boolType ?? $this->boolType, $isNot ?? $this->isNot, Where::TYPE_WRAPPED);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return SharQ
     */
    public function whereWrapped(\Closure $callback): SharQ
    {// 2023-05-09
        return $this->_whereWrapped(null, null, $callback);
    }

    /**
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    private function _whereExists($value): SharQ
    {// 2023-06-02
        $iWhere = new Where(null, null, $value, $this->boolType, $this->isNot, Where::TYPE_EXISTS);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param \Closure|SharQ $callback
     * @return SharQ
     */
    public function whereExists($callback): SharQ
    {// 2023-05-09
        $this->isNot    = false;
        $this->boolType = self::BOOL_TYPE_AND;

        return $this->_whereExists($callback);
    }

    /**
     * @param \Closure|SharQ $callback
     * @return SharQ
     */
    public function whereNotExists($callback): SharQ
    {// 2023-05-09
        $this->isNot    = true;
        $this->boolType = self::BOOL_TYPE_AND;

        return $this->_whereExists($callback);
    }

    /**
     * @param \Closure $callback
     * @return SharQ
     */
    public function orWhereExists(\Closure $callback): SharQ
    {// 2023-05-07
        $this->isNot    = false;
        $this->boolType = self::BOOL_TYPE_OR;

        return $this->_whereExists($callback);
    }

    /**
     * @param Closure $callback
     * @return SharQ
     */
    public function orWhereNotExists(\Closure $callback): SharQ
    {// 2023-05-09
        $this->isNot    = true;
        $this->boolType = self::BOOL_TYPE_OR;

        return $this->_whereExists($callback);
    }
    /**
     * @param mixed $column
     * @param mixed $values
     */
    private function _whereIn($column, $values, ?string $boolType, ?bool $isNot)
    {
        // if(is_array($values) && count($values) === 0)
        // {
        //     $bool = $this->isNot;
        //     $this->isNot = false;
        //
        //     return $this->where($bool);
        // }
        // // if(is_array($column) && count($column) !== count($values[0])) throw new \Exception('The number of columns does not match the number of values');
        //
        // $this->whereFlag = Where::TYPE_IN;
        //
        // $iWhere = new Where($column, null, $values, $this->boolType, $this->isNot, Where::TYPE_IN);
        //
        // $this->iStatements[] = $iWhere;
        //
        // return $this;


        if (is_array($values) && count($values) === 0)
        {
            return $this->_whereRaw($boolType, false, ($isNot ? '1' : '0').' = 1');
        }

        // return $this->_where(Where::TYPE_IN, $boolType, $isNot, $column, $values);

        $iWhere = new Where($column, null, $values, $boolType ?? $this->boolType, $isNot ?? $this->isNot, Where::TYPE_IN);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function whereIn($column, $values): SharQ
    {// 2023-05-09
        return $this->_whereIn($column, $values, null, null);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function whereNotIn($column, $values): SharQ
    {// 2023-05-09
        // $this->isNot = true;
        // return $this->whereIn($column, $values);

        return $this->_whereIn($column, $values, null, true);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function andWhereIn($column, $values): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_IN;
        // $this->boolType = self::BOOL_TYPE_AND;
        // return $this->whereIn($column, $values);

        return $this->_whereIn($column, $values, self::BOOL_TYPE_AND, null);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function andWhereNotIn($column, $values): SharQ
    {// 2023-05-09
        // $this->isNot = true;
        // return $this->andWhereIn($column, $values);

        return $this->_whereIn($column, $values, self::BOOL_TYPE_AND, true);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function orWhereIn($column, $values): SharQ
    {// 2023-05-09
        // $this->whereFlag = Where::TYPE_IN;
        // $this->boolType = self::BOOL_TYPE_OR;
        // return $this->whereIn($column, $values);

        return $this->_whereIn($column, $values, self::BOOL_TYPE_OR, null);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function orWhereNotIn($column, $values): SharQ
    {// 2023-05-09
        // $this->isNot = true;
        // return $this->orWhereIn($column, $values);
        //
        return $this->_whereIn($column, $values, self::BOOL_TYPE_OR, true);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    private function _whereNull(?string $boolType, ?bool $isNot, $column): SharQ
    {// 2023-06-02
        $iWhere = new Where($column, null, null, $boolType ?? $this->boolType, $isNot ?? $this->isNot, Where::TYPE_NULL);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function whereNull($column): SharQ
    {// 2023-05-09
        // $this->isNot = false;
        // $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull(self::BOOL_TYPE_AND, false, $column);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function whereNotNull($column): SharQ
    {// 2023-05-09
        // $this->isNot = true;
        // $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull(self::BOOL_TYPE_AND, true, $column);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orWhereNull($column): SharQ
    {// 2023-05-09
        // $this->isNot = false;
        // $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull(self::BOOL_TYPE_OR, false, $column);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orWhereNotNull($column): SharQ
    {// 2023-05-09
        // $this->isNot = true;
        // $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull(self::BOOL_TYPE_OR, true, $column);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @param string|Raw|SharQ|\Closure $type
     * @return SharQ
     */
    private function _whereLike($column, $value, $type): SharQ
    {// 2023-06-01
        $iWhere = new Where($column, null, $value, $this->boolType, $this->isNot, $type);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function whereLike($column, $value): SharQ
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function andWhereLike($column, $value): SharQ
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;

        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function orWhereLike($column, $value): SharQ
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;

        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function whereILike($column, $value): SharQ
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function andWhereILike($column, $value): SharQ
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;

        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $value
     * @return SharQ
     */
    public function orWhereILike($column, $value): SharQ
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;

        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    private function _whereBetween(?string $boolType, ?bool $isNot, $column, $values): SharQ
    {// 2023-06-01
        if (count($values) !== 2)
        {
            throw new \InvalidArgumentException('whereBetween() expects exactly 2 values');
        }

        $iWhere = new Where($column, null, $values, $boolType ?? $this->boolType, $isNot ?? $this->isNot, Where::TYPE_BETWEEN);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function whereBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(null, null, $column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function whereNotBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(null, true, $column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function andWhereBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(self::BOOL_TYPE_AND, null, $column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function andWhereNotBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(self::BOOL_TYPE_AND, true, $column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function orWhereBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(self::BOOL_TYPE_OR, null, $column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ|\Closure> $values
     * @return SharQ
     */
    public function orWhereNotBetween($column, $values): SharQ
    {// 2023-06-01
        return $this->_whereBetween(self::BOOL_TYPE_OR, true, $column, $values);
    }

    /**
     * Helper for compiling any aggregate queries.
     * @param string|Raw $method
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return SharQ
     */
    private function aggregate($method, $column, array $options = []): SharQ
    {// 2023-05-26
        $type = Columns::TYPE_AGGREGATE;

        if ($column instanceof Raw)
        {
            $type = Columns::TYPE_AGGREGATE_RAW;
        }

        if (!is_array($column))
        {
            $column = [$column];
        }

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
     * @return SharQ
     */
    public function count($column = null, array $options = []): SharQ
    {// 2023-05-26
        return $this->aggregate('COUNT', $column ?? '*', $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return SharQ
     */
    public function countDistinct(...$columns): SharQ
    {// 2023-05-26
        $options = [];

        if (count($columns) > 1 && is_array(end($columns)) && is_string(key(end($columns))))
        {
            $options = array_pop($columns);
        }

        if (count($columns) === 0)
        {
            $columns = ['*'];
        }

        $options['distinct'] = true;

        return $this->aggregate('COUNT', $columns, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return SharQ
     */
    public function min($column, $options = []): SharQ
    {// 2023-05-26
        return $this->aggregate('MIN', $column, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return SharQ
     */
    public function max($column, $options = []): SharQ
    {// 2023-05-26
        return $this->aggregate('MAX', $column, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return SharQ
     */
    public function sum($column, $options = []): SharQ
    {// 2023-05-26
        return $this->aggregate('SUM', $column, $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return SharQ
     */
    public function sumDistinct(...$columns): SharQ
    {// 2023-05-26
        $options = [];

        if (is_array(end($columns)))
        {
            $options = array_pop($columns);
        }

        if (count($columns) === 0)
        {
            $columns = ['*'];
        }

        $options['distinct'] = true;

        return $this->aggregate('SUM', $columns, $options);
    }

    /**
     * @param string|Raw $column
     * @param array<string, mixed> $options
     * @return SharQ
     */
    public function avg($column, $options = []): SharQ
    {// 2023-05-26
        return $this->aggregate('AVG', $column, $options);
    }

    /**
     * @param array<int, string|Raw|array<string, mixed> $columns [...column, options]
     * @return SharQ
     */
    public function avgDistinct(...$columns): SharQ
    {// 2023-05-26
        $options = [];

        if (is_array(end($columns)))
        {
            $options = array_pop($columns);
        }

        if (count($columns) === 0)
        {
            $columns = ['*'];
        }

        $options['distinct'] = true;

        return $this->aggregate('AVG', $columns, $options);
    }

    /**
     * see Union::TYPE_* constants
     * @param string $type Union::TYPE_* constant
     * @param array<int, \Closure|SharQ> $args
     * @return SharQ
     */
    private function _union(string $type, ...$args): SharQ
    {// 2023-06-02
        $wrap = array_pop($args);

        if (!is_bool($wrap))
        {
            $args[] = $wrap;
            $wrap   = false;
        }

        $callbacks = $args;

        if (is_array($args[0]))
        {
            $callbacks = $args[0];
        }

        foreach ($callbacks as $callback)
        {
            $iUnion              = new Union($type, $callback, $wrap ?? false);
            $this->iStatements[] = $iUnion;
        }

        return $this;
    }

    /**
     * @param array<int, \Closure|SharQ> $args
     * @return SharQ
     */
    public function union(...$args): SharQ
    {// 2023-06-02
        return $this->_union(Union::TYPE_BASIC, ...$args);
    }

    /**
     * @param array<int, \Closure|SharQ> $args
     * @return SharQ
     */
    public function unionAll(...$args): SharQ
    {// 2023-06-02
        return $this->_union(Union::TYPE_ALL, ...$args);
    }

    /**
     * @param array<int, \Closure|SharQ> $args
     * @return SharQ
     */
    public function intersect(...$args): SharQ
    {// 2023-06-02
        return $this->_union(Union::TYPE_INTERSECT, ...$args);
    }

    /**
     * @param int|Raw|SharQ $value
     * @param array<int,mixed> $options
     * @return SharQ
     */
    public function offset($value, ...$options): SharQ
    {// 2023-05-26
        if ($value === null || $value instanceof Raw || $value instanceof SharQ)
        {
            $this->iSingle->offset = $value;
        }
        else
        {
            if (!is_integer($value))
            {
                throw new \InvalidArgumentException('Offset must be an integer.');
            }

            if ($value < 0)
            {
                throw new \InvalidArgumentException('Offset must be greater than or equal to 0.');
            }

            $this->iSingle->offset = $value;
        }

        // this._setSkipBinding('limit', options);

        return $this;
    }

    /**
     * @param int|Raw|SharQ $value
     * @param array<int,mixed> $options
     * @return SharQ
     */
    public function limit($value, ...$options): SharQ
    {// 2023-05-26
        $this->iSingle->limit = $value;
        // this._setSkipBinding('limit', options);

        return $this;
    }

    /**
     * @return SharQ
     */
    public function clone(): SharQ
    {// 2023-06-02j
        return clone $this;
    }

    /**
     * Sets method to DELETE
     * @return SharQ
     * @param mixed $tables
     */
    public function delete(...$tables): SharQ
    {// 2023-06-02
        $this->method = self::METHOD_DELETE;

        $tables = $this->_normalizeColumns($tables);

        if (count($tables) !== 0)
        {
            $this->iSingle->delete = $tables;
        }

        return $this;
    }

    /**
     * see Group::TYPE_* constants
     * @param string $type Group::TYPE_* constant
     * @param string|Raw $column
     * @return SharQ
     */
    private function _groupBy(string $type, $column): SharQ
    {// 2023-06-05
        $iGroupBy = new Group($type, $column);

        $this->iStatements[] = $iGroupBy;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $columns
     * @return SharQ
     */
    public function groupBy(...$columns): SharQ
    {// 2023-06-05
        foreach ($columns as $column)
        {
            if (!($column instanceof Raw))
            {
                $this->_groupBy(Group::TYPE_BASIC, $column);
            }
            else
            {
                $this->_groupBy(Group::TYPE_RAW, $column);
            }
        }

        return $this;
    }

    /**
     * @param array<int, string> $columns
     * @return SharQ
     */
    public function groupByRaw(...$columns): SharQ
    {// 2023-06-05
        if (count($columns) === 0)
        {
            throw new \InvalidArgumentException('groupByRaw() requires at least one argument.');
        }

        foreach ($columns as $column)
        {
            if (!($column instanceof Raw))
            {
                $this->_groupBy(Group::TYPE_RAW, new Raw($column));
            }
            else
            {
                $this->_groupBy(Group::TYPE_RAW, $column);
            }
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
     * @return SharQ
     */
    private function _orderBy(string $type, $column, $direction = null, $nullsPosition = null): SharQ
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
     * @return SharQ
     */
    public function orderBy($column, $direction = Order::DIRECTION_ASC, $nullsPosition = null): SharQ
    {// 2023-06-05
        if ($column instanceof Raw)
        {
            return $this->orderByRaw($column, $direction);
        }

        if (is_string($column) || $column instanceof SharQ)
        {
            return $this->_orderBy(Order::TYPE_BASIC, $column, $direction, $nullsPosition);
        }

        if (is_array($column))
        {
            foreach ($column as $key => $value)
            {
                if (is_string($key))
                {
                    $this->orderBy($key, $value, $nullsPosition);
                }
                else
                {
                    if (is_string($value))
                    {
                        $this->orderBy($value, $direction, $nullsPosition);
                    }
                    else
                    {
                        $this->orderBy($value['column'], $value['order'] ?? Order::DIRECTION_ASC, $value['nulls'] ?? null);
                    }
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
     * @return SharQ
     */
    public function orderByRaw($column, $direction = null): SharQ
    {// 2023-06-05
        if (!($column instanceof Raw))
        {
            $column = new Raw($column);
        }

        return $this->_orderBy(Order::TYPE_RAW, $column, $direction);
    }

    /**
     * see Having::TYPE_* constants
     * see SharQ::BOOL_TYPE_* constants
     *
     * @param string $type Having::TYPE_* constant
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure $operator
     * @param string|Raw|SharQ|\Closure $value
     * @param string $boolean SharQ::BOOL_TYPE_* constant
     * @param bool $isNot
     * @return SharQ
     */
    private function _having(string $type, $column, $operator, $value, $boolean, $isNot): SharQ
    {// 2023-06-05
        $iHaving = new Having($type, $column, $operator, $value, $boolean, $isNot);

        $this->iStatements[] = $iHaving;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure|null $operator
     * @param string|Raw|SharQ|\Closure|null $value
     * @return SharQ
     */
    public function having($column, $operator = null, $value = null): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        if ($column instanceof Raw)
        {
            return $this->havingRaw($column);
        }

        if ($column instanceof \Closure)
        {
            return $this->havingWrapped($column);
        }

        return $this->_having(Having::TYPE_BASIC, $column, $operator, $value, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param string|Raw|SharQ|\Closure|null $operator
     * @param string|Raw|SharQ|\Closure|null $value
     * @return SharQ
     */
    public function orHaving($column, $operator = null, $value = null): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        if ($column instanceof Raw)
        {
            return $this->orHavingRaw($column);
        }

        if ($column instanceof \Closure)
        {
            return $this->orHavingWrapped($column);
        }

        return $this->_having(Having::TYPE_BASIC, $column, $operator, $value, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function havingNull($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orHavingNull($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function havingNotNull($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = true;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orHavingNotNull($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = true;

        return $this->_having(Having::TYPE_NULL, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function havingExists($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orHavingExists($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function havingNotExists($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = true;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     */
    public function orHavingNotExists($column): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = true;

        return $this->_having(Having::TYPE_EXISTS, $column, null, null, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ> $values
     * @return SharQ
     */
    private function _havingBetween($column, array $values): SharQ
    {// 2023-06-05
        if (count($values) !== 2)
        {
            throw new \InvalidArgumentException('You must specify 2 values for the havingBetween clause');
        }

        return $this->_having(Having::TYPE_BETWEEN, $column, null, $values, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     * @param array<int,mixed> $values
     */
    public function havingBetween($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @return SharQ
     * @param array<int,mixed> $values
     */
    public function orHavingBetween($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ> $values
     * @return SharQ
     */
    public function havingNotBetween($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = true;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw|SharQ> $values
     * @return SharQ
     */
    public function orHavingNotBetween($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = true;

        return $this->_havingBetween($column, $values);
    }

    /**
     * @param string|Raw $value
     * @return SharQ
     */
    public function havingRaw($value): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        $iRaw = $value;

        if (!($value instanceof Raw))
        {
            $iRaw = new Raw($value);
        }

        return $this->_having(Having::TYPE_RAW, null, null, $iRaw, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $value
     * @return SharQ
     */
    public function orHavingRaw($value): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        $iRaw = $value;

        if (!($value instanceof Raw))
        {
            $iRaw = new Raw($value);
        }

        return $this->_having(Having::TYPE_RAW, null, null, $iRaw, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return SharQ
     */
    private function _havingIn($column, array $values): SharQ
    {// 2023-06-05
        if (count($values) === 0)
        {
            return $this;
        }

        return $this->_having(Having::TYPE_IN, $column, null, $values, $this->boolType, $this->isNot);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return SharQ
     */
    public function havingIn($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return SharQ
     */
    public function orHavingIn($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return SharQ
     */
    public function havingNotIn($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = true;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param string|Raw $column
     * @param array<int, string|Raw> $values
     * @return SharQ
     */
    public function orHavingNotIn($column, array $values): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = true;

        return $this->_havingIn($column, $values);
    }

    /**
     * @param \Closure $callback
     * @return SharQ
     */
    public function havingWrapped(\Closure $callback): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_AND;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_WRAPPED, null, null, $callback, $this->boolType, $this->isNot);
    }

    /**
     * @param \Closure $callback
     * @return SharQ
     */
    public function orHavingWrapped(\Closure $callback): SharQ
    {// 2023-06-05
        $this->boolType = self::BOOL_TYPE_OR;
        $this->isNot    = false;

        return $this->_having(Having::TYPE_WRAPPED, null, null, $callback, $this->boolType, $this->isNot);
    }

    /**
     * Sets the values for an `update`, allowing for both
     * Support for `.update(key, value, [returning])` and `.update(obj, [returning])` syntaxes.
     *
     * @param array<int, string|Raw|SharQ>|string $args [values, returning, options]
     * @return SharQ
     */
    public function update(...$args): SharQ
    {// 2023-06-05
        $values    = $args[0] ?? null;
        $returning = $args[1] ?? null;
        $options   = $args[2] ?? null;

        $this->method = self::METHOD_UPDATE;

        $data = [];
        $ret  = null;

        if (is_string($values))
        {
            $data[$values] = $returning;

            if (func_num_args() > 2)
            {
                $ret = func_get_arg(2);
            }
        }
        else
        {
            foreach ($values as $key => $value)
            {
                $data[$key] = $value;
            }

            if (func_num_args() > 1)
            {
                $ret = func_get_arg(1);
            }
        }

        if ($ret)
        {
            $this->returning($ret, $options);
        }

        $this->iSingle->update = $data;

        return $this;
    }

    /**
     * @param array<int, string|Raw|SharQ> $args [values, returning, options]
     * @return SharQ
     */
    public function insert(...$args): SharQ
    {// 2023-06-06
        $values    = $args[0] ?? null;
        $returning = $args[1] ?? null;
        $options   = $args[2] ?? null;

        if (func_num_args() === 0)
        {
            throw new \InvalidArgumentException('insert() must be called with at least one argument');
        }

        if (is_array($values) && count($values) === 0)
        {
            throw new \InvalidArgumentException('insert() must be called with at least one argument');
        }

        $this->method = self::METHOD_INSERT;

        if ($returning)
        {
            $this->returning($returning, $options);
        }

        $this->iSingle->insert = $values;

        return $this;
    }

    /**
     * @param string|array<int, string|Raw> $columns
     * @return OnConflictBuilder
     */
    public function onConflict($columns): OnConflictBuilder
    {// 2023-06-06
        if (is_string($columns))
        {
            $columns = [$columns];
        }

        return new OnConflictBuilder($this, $columns);
    }

    /**
     * Not supported in MySQL, and will have no effect
     * @param string|Raw|array<int, string|Raw> $returning
     * @param array<string, mixed> $options
     * @return SharQ
     */
    public function returning($returning, $options = []): SharQ
    {// 2023-06-05
        if (!is_array($returning))
        {
            $returning = [$returning];
        }

        $this->iSingle->returning = $returning;
        $this->iSingle->options   = $options;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param int|float $amount
     * @return SharQ
     */
    private function _counter($column, $amount): SharQ
    {// 2023-06-05
        $this->method = self::METHOD_UPDATE;

        $this->iSingle->counter = $this->iSingle->counter ?? [];

        $this->iSingle->counter[$column] = $amount;

        return $this;
    }

    /**
     * @param string|Raw $column
     * @param int|float $amount
     * @return SharQ
     */
    public function increment($column, $amount = null): SharQ
    {// 2023-06-05
        if (is_array($column))
        {
            foreach ($column as $key => $value)
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
     * @return SharQ
     */
    public function decrement($column, $amount = null): SharQ
    {// 2023-06-05
        if (is_array($column))
        {
            foreach ($column as $key => $value)
            {
                $this->_counter($key, -$value);
            }

            return $this;
        }

        return $this->_counter($column, -$amount);
    }

    /**
     * @param string|Raw $table
     * @return SharQ
     */
    public function truncate($table = null): SharQ
    {// 2023-06-06
        $this->method = self::METHOD_TRUNCATE;

        if ($table)
        {
            return $this->table($table);
        }

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return SharQ
     */
    public function forUpdate(...$tables): SharQ
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_UPDATE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return SharQ
     */
    public function forShare(...$tables): SharQ
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_SHARE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return SharQ
     */
    public function forNoKeyUpdate(...$tables): SharQ
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_NO_KEY_UPDATE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @param array<int, string|Raw> $tables
     * @return SharQ
     */
    public function forKeyShare(...$tables): SharQ
    {// 2023-06-07
        $this->iSingle->lock = self::LOCK_MODE_FOR_KEY_SHARE;

        $this->iSingle->lockTables = $tables;

        return $this;
    }

    /**
     * @internal Helper method
     * @return bool
     */
    protected function isSelectQuery(): bool
    {// 2023-06-07
        return in_array($this->getMethod(), [self::METHOD_SELECT, self::METHOD_FIRST, self::METHOD_PLUCK]);
    }

    /**
     * Skips locked rows when using a lock constraint.
     * @return SharQ
     */
    public function skipLocked(): SharQ
    {// 2023-06-07
        if (!$this->isSelectQuery())
        {
            throw new \LogicException("Cannot chain ->skipLocked() on \"{$this->getMethod()}\" query!");
        }

        if ($this->iSingle->lock === null)
        {
            throw new \LogicException('->skipLocked() can only be used after a call to ->forShare() or ->forUpdate()!');
        }

        if ($this->iSingle->waitMode === self::WAIT_MODE_NO_WAIT)
        {
            throw new \LogicException('->skipLocked() cannot be used together with ->noWait()!');
        }

        $this->iSingle->waitMode = self::WAIT_MODE_SKIP_LOCKED;

        return $this;
    }

    /**
     * Causes error when acessing a locked row instead of waiting for it to be released.
     * @return SharQ
     */
    public function noWait(): SharQ
    {// 2023-06-07
        if (!$this->isSelectQuery())
        {
            throw new \LogicException("Cannot chain ->noWait() on \"{$this->getMethod()}\" query!");
        }

        if ($this->iSingle->lock === null)
        {
            throw new \LogicException('->noWait() can only be used after a call to ->forShare() or ->forUpdate()!');
        }

        if ($this->iSingle->waitMode === self::WAIT_MODE_SKIP_LOCKED)
        {
            throw new \LogicException('->noWait() cannot be used together with ->skipLocked()!');
        }

        $this->iSingle->waitMode = self::WAIT_MODE_NO_WAIT;

        return $this;
    }

    /**
     * @param \Closure $callback
     * @param array<int, mixed> $args
     */
    public function modify(\Closure $callback, ...$args): SharQ
    {// 2023-06-07
        $callback($this, ...$args);

        return $this;
    }

    public function transacting(?Transaction &$iTransaction = null): SharQ
    {// 2023-06-07
        $this->iSingle->transaction = &$iTransaction;

        return $this;
    }
    /**
     * @param mixed $bindings
     */
    public function raw(string $raw, ...$bindings): SharQ
    {// 2023-06-07
        $this->method = self::METHOD_RAW;

        $iRaw = new Raw($raw, ...$bindings);

        $this->iStatements[] = $iRaw;

        return $this;
    }

    public function fetchMode(int $fetchMode): SharQ
    {// 2023-08-03
        static $validFetchModes =
        [
            self::FETCH_MODE_ASSOCIATIVE => \PDO::FETCH_ASSOC,
            self::FETCH_MODE_OBJECT      => \PDO::FETCH_OBJ,
            self::FETCH_MODE_NUMBERED    => \PDO::FETCH_NUM,
            self::FETCH_MODE_KEY_PAIR    => \PDO::FETCH_KEY_PAIR,
            self::FETCH_MODE_COLUMN      => \PDO::FETCH_COLUMN
        ];

        if (!isset($validFetchModes[$fetchMode]))
        {
            Throw new \LogicException('invalid fetch mode provided.');
        }

        $this->fetchMode = $validFetchModes[$fetchMode];

        return $this;
    }

    public function fetchMethod(string $fetchMethod): SharQ
    {// 2023-08-03
        static $validFetchMethods =
        [
            self::FETCH_METHOD_ALL,
            self::FETCH_METHOD_GENERATOR,
        ];

        if (!in_array($fetchMethod, $validFetchMethods))
        {
            Throw new \LogicException('invalid fetch method provided.');
        }

        $this->fetchMethod = $fetchMethod;

        return $this;
    }

    /**
     * @return array<int, mixed>|mixed
     * @throws \PDOException
     */
    public function run()
    {// 2023-06-12
        $iQuery = $this->toQuery();

        $statement = $this->iClient->query($iQuery);

        if (in_array($this->getMethod(), [self::METHOD_INSERT]))
        {
            return $this->iClient->getLastInsertId();
        }

        if (in_array($this->getMethod(), [self::METHOD_UPDATE, self::METHOD_DELETE]))
        {
            return $statement->rowCount();
        }

        if ($this->fetchMethod === self::FETCH_METHOD_ALL)
        {
            $result = ($this->getMethod() === self::METHOD_FIRST)
                ? $statement->fetch($this->fetchMode)
                : $statement->fetchAll($this->fetchMode);

            $statement->closeCursor();

            if ($result === false)
            {
                return null;
            }

            return $result;
        }
        else if ($this->fetchMethod === self::FETCH_METHOD_GENERATOR)
        {
            $generator = new SharQResultGenerator($statement);

            $statement->closeCursor();

            return $generator;
        }
    }

    public function toQuery(): Query
    {// 2023-06-12
        $iSharQCompiler = new SharQCompiler($this->iClient, $this, []);

        return $iSharQCompiler->toQuery();
    }

    public function __clone()
    {
        foreach (get_object_vars($this) as $name => $value)
        {
            if (is_object($value))
            {
                $this->{$name} = clone $value;
            }
        }
    }
}
