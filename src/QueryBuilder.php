<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

use Sharksmedia\QueryBuilder\Client;

use Sharksmedia\QueryBuilder\Statement\IStatement;
use Sharksmedia\QueryBuilder\Statement\IAliasable;

use Sharksmedia\QueryBuilder\Statement\Columns;
use Sharksmedia\QueryBuilder\Statement\HintComments;
use Sharksmedia\QueryBuilder\Statement\Join;
use Sharksmedia\QueryBuilder\Statement\Where;
use Sharksmedia\QueryBuilder\Statement\Union;
use Sharksmedia\QueryBuilder\Statement\Raw;

/**
 * 2023-05-08
 * Used for options which can only have 1 value
 * @property ?string $table
 */
class Single
{
    public /* ?string */ $schema = null;
    public /* ?string */ $table = null;
    public ?string $columnMethod = null;
    public         $limit = null;
    public         $offset = null;
}

/**
 * 2023-05-08
 * @property Client $iClient
 * @property IStatement[] $iStatements
 *
 * @property string $schema
 * @property string $joinFlag
 * @property string $whereFlag
 * @property string $boolType
 * @property bool $isNot
 */
class QueryBuilder
{
    public const BOOL_TYPE_AND = 'AND';
    public const BOOL_TYPE_OR = 'OR';

    private Client $iClient;
    private array $iStatements = [];
    private Single $iSingle;

    private string $schema;
    private string $joinFlag = Join::TYPE_INNER;
    private string $whereFlag = Where::TYPE_BASIC;
    private string $boolType = self::BOOL_TYPE_AND;
    private bool   $isNot = false;

    public function __construct(Client $iClient, string $schema)
    {// 2023-05-08
        $this->iClient = $iClient;
        $this->schema = $schema;
        $this->iSingle = new Single();
    }
    
    /**
     * 2023-05-08
     * @return string
     */
    public function __toString(): string
    {// 2023-05-08
        return $this->toQuery();
    }

    public function getContext(): string
    {// 2023-05-10
        return 'query';
    }

    public function getSchema(): string
    {// 2023-05-10
        return $this->schema;
    }

    public function getSelectMethod(): ?string
    {// 2023-05-15
        return $this->iSingle->columnMethod;
    }

    private function clearGrouping(string $type): self
    {// 2023-05-15
        $this->iStatements = array_filter($this->iStatements, function($statement) use($type)
        {
            return !($statement instanceof $type);
        });

        return $this;
    }

    public function clearWhere(): self
    {// 2023-06-01
        return $this->clearGrouping(Where::class);
    }

    /**
     * 2023-05-08
     * Get single options
     * @return string
     */
    public function getSingle(): Single
    {// 2023-05-15
        return $this->iSingle;
    }
    /**
     * @param mixed $tableName
     */
    public function table($tableName): QueryBuilder
    {// 2023-05-15
        $this->iSingle->table = $tableName;

        return $this;
    }
    /**
     * @param mixed $tableName
     */
    public function from($tableName): QueryBuilder
    {// 2023-05-15
        return $this->table($tableName);
    }
    /**
     * @param mixed $tableName
     */
    public function into($tableName): QueryBuilder
    {// 2023-05-15
        return $this->table($tableName);
    }
    /**
     * @param mixed $schemaName
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
    public function timeout(int $milliSeconds, bool $cancel=false): self
    {// 2023-05-08
        // 2023-05-08 TODO: implement me
        if($milliSeconds < 0) throw new \UnexpectedValueException('Timeout must be a positive integer');

        return $this;
    }

    /**
     * 2023-05-08
     * @return IStatement|IAliasable
     */
    private function getLastStatement(): IStatement
    {// 2023-05-09
        return end($this->iStatements);
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
     * @param string|Raw[] ...$columns One or more values
     * @return self
     * @param mixed $columns
     */
    public function column(...$columns): QueryBuilder
    {// 2023-05-08
        return $this->_column($columns, Columns::TYPE_PLUCK);
    }
    /**
     * @param array<int,mixed> $columns
     */
    private function _normalizeColumns(array $columns): array
    {
        if(count($columns) === 1 && is_array($columns[0]) && is_integer(key($columns[0]))) $columns = $columns[0];
        
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

        return $columns;
    }

    /**
     * @param array<int,mixed> $columns
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
     * @param string|Raw[] ...$columns One or more values
     * @return self
     */
    public function distinct(string ...$columns): self
    {// 2023-05-08
        $columns = $this->_normalizeColumns($columns);

        $iColumns = new Columns(null, $columns);
        $iColumns->distinct(true);

        $this->iStatements[] = $iColumns;

        return $this;
    }

    /**
     * 2023-05-08
     * @param string[] ...$columns One or more values
     * @return self
     */
    public function distinctOn(string ...$columns): self
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
     * @return self
     */
    public function as(string $alias): self
    {// 2023-05-09
        $iStatement = $this->getLastStatement();

        if(!$iStatement instanceof IAliasable) throw new \UnexpectedValueException('Statement is not aliasable');

        $iStatement->as($alias);

        return $this;
    }

    /**
     * 2023-05-08
     * @param string[] ...$hintComments One or more values
     * @return self
     */
    public function hintComment(string ...$hintComments): self
    {// 2023-05-09
        $iHintComments = new HintComments($hintComments);

        $this->iStatements[] = $iHintComments;

        return $this;
    }

    /**
     * @param string|Raw[] $columns
     */
    public function select(...$columns): QueryBuilder
    {// 2023-05-15
        $this->iSingle->columnMethod = Columns::TYPE_PLUCK;

        return $this->_column($columns, Columns::TYPE_PLUCK);
    }

    /**
     * Sets the values for a `select` query, informing that only the first
     * row should be returned (limit 1).
     * @param string|Raw[] $columns
     */
    public function first(...$columns): QueryBuilder
    {// 2023-05-15
        $this->iSingle->columnMethod = Columns::TYPE_FIRST;

        return $this->_column($columns, Columns::TYPE_PLUCK)->limit(1);
    }
    /**
     * 2023-05-08
     *
     * join(string $table, string $first, string $operator, string $second)
     * join(string $table, callable $first)
     *
     * @param string $table
     * @param string|callable $first
     * @param string[] ...$args One or more values
     * @return self
     * @param mixed $args
     */
    public function join(string $table, $first=null, ...$args): QueryBuilder
    {// 2023-05-09
        $iJoin = null;
        
        if(is_callable($first))
        {
            $iJoin = new Join($table, $this->joinFlag, $this->schema);
            $first($iJoin);
        }
        else if($this->joinFlag === Join::TYPE_RAW)
        {
            $iJoin = new Join($this->iClient->raw($table, $first), Join::TYPE_RAW);
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

        $this->iStatements[] = $iJoin;

        return $this;
    }
    /**
     * @param mixed $args
     */
    public function innerJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_INNER;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function leftJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_LEFT;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function leftOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_LEFT_OUTER;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function rightJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RIGHT;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function rightOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RIGHT_OUTER;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function outerJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_OUTER;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function fullOuterJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_FULL_OUTER;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function crossJoin(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_CROSS;
        return $this->join(...$args);
    }
    /**
     * @param mixed $args
     */
    public function joinRaw(...$args): QueryBuilder
    {// 2023-05-09
        $this->joinFlag = Join::TYPE_RAW;
        return $this->join(...$args);
    }

    // Where modifiers:
    public function or(): self
    {// 2023-05-09
        $this->boolType = self::BOOL_TYPE_OR;
        return $this;
    }

    public function not(): self
    {// 2023-05-09
        $this->isNot = true;
        return $this;
    }
    /**
     * @param mixed $column
     * @param mixed $value
     * @param mixed $args
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
            foreach($column as $columnName=>$value)
            {
                $this->andWhere($columnName, '=', $value);
            }

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
     * @param mixed $args
     */
    public function whereColumn(...$args): QueryBuilder
    {// 2023-06-01
        $this->whereFlag = Where::TYPE_COLUMN;
        return $this->where(...$args);
    }

    /**
     * @param mixed $column
     * @param mixed $args
     */
    public function andWhere($column, ...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->where($column, ...$args);
    }
    /**
     * @param mixed $column
     * @param mixed $args
     */
    public function orWhere($column, ...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->where($column, ...$args);
    }
    /**
     * @param mixed $column
     * @param mixed $args
     */
    public function whereNot(...$args): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_BASIC;
        $this->isNot = true;
        return $this->where(...$args);
    }
    /**
     * @param mixed $column
     * @param mixed $args
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
     * @param callable(): mixed $callback
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
     * @param mixed $value
     */
    private function _whereExists($value): QueryBuilder
    {// 2023-06-02
        $iWhere = new Where(null, null, $value, $this->boolType, $this->isNot, Where::TYPE_EXISTS);

        $this->iStatements[] = $iWhere;

        return $this;

    }

    /**
     * @param callable()|QueryBuilder: mixed $callback
     * @param mixed $callback
     */
    public function whereExists($callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereExists($callback);
    }
    /**
     * @param callable()|QueryBuilder: mixed $callback
     * @param mixed $callback
     */
    public function whereNotExists($callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereExists($callback);
    }
    /**
     * @param callable(): mixed $callback
     */
    public function orWhereExists(callable $callback): QueryBuilder
    {// 2023-05-07
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereExists($callback);
    }
    /**
     * @param callable(): mixed $callback
     */
    public function orWhereNotExists(callable $callback): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereExists($callback);
    }
    /**
     * @param array<int,mixed> $values
     * @param mixed $column
     */
    public function whereIn($column, $values): QueryBuilder
    {// 2023-05-09
        if(is_array($values) && count($values) === 0) return $this->where($this->isNot);
        // if(is_array($column) && count($column) !== count($values[0])) throw new \Exception('The number of columns does not match the number of values');

        $this->whereFlag = Where::TYPE_IN;

        $iWhere = new Where($column, null, $values, $this->boolType, $this->isNot, Where::TYPE_IN);

        $this->iStatements[] = $iWhere;

        return $this;
    }
    /**
     * @param array<int,mixed> $values
     */
    public function whereNotIn(string $column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->whereIn($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function andWhereIn(string $column, $values): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_IN;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->whereIn($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function andWhereNotIn(string $column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->andWhereIn($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orWhereIn(string $column, $values): QueryBuilder
    {// 2023-05-09
        $this->whereFlag = Where::TYPE_IN;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->whereIn($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orWhereNotIn(string $column, $values): QueryBuilder
    {// 2023-05-09
        $this->isNot = true;
        return $this->orWhereIn($column, $values);
    }

    private function _whereNull(string $column): self
    {// 2023-06-02
        $iWhere = new Where($column, null, null, $this->boolType, $this->isNot, Where::TYPE_NULL);

        $this->iStatements[] = $iWhere;

        return $this;
    }

    public function whereNull(string $column): self
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull($column);
    }

    public function whereNotNull(string $column): self
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereNull($column);
    }

    public function orWhereNull(string $column): self
    {// 2023-05-09
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull($column);
    }

    public function orWhereNotNull(string $column): self
    {// 2023-05-09
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereNull($column);
    }
    /**
     * @param mixed $column
     * @param mixed $value
     * @param mixed $type
     */
    private function _whereLike($column, $value, $type): QueryBuilder
    {// 2023-06-01
        $iWhere = new Where($column, null, $value, $this->boolType, $this->isNot, $type);

        $this->iStatements[] = $iWhere;

        return $this;
    }
    /**
     * @param mixed $value
     */
    public function whereLike(string $column, $value): QueryBuilder
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }
    /**
     * @param mixed $value
     */
    public function andWhereLike(string $column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }
    /**
     * @param mixed $column
     * @param mixed $value
     */
    public function orWhereLike($column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereLike($column, $value, Where::TYPE_LIKE);
    }
    /**
     * @param mixed $value
     */
    public function whereILike(string $column, $value): QueryBuilder
    {// 2023-06-01
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }
    /**
     * @param mixed $value
     */
    public function andWhereILike(string $column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }
    /**
     * @param mixed $value
     */
    public function orWhereILike(string $column, $value): QueryBuilder
    {// 2023-06-01
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereLike($column, $value, Where::TYPE_ILIKE);
    }
    /**
     * @param array<int,mixed> $values
     */
    private function _whereBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        if(count($values) !== 2) throw new \InvalidArgumentException('whereBetween() expects exactly 2 values');

        $iWhere = new Where($column, null, $values, $this->boolType, $this->isNot, Where::TYPE_BETWEEN);

        $this->iStatements[] = $iWhere;

        return $this;
    }
    /**
     * @param array<int,mixed> $values
     */
    public function whereBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        return $this->_whereBetween($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function whereNotBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        return $this->_whereBetween($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function andWhereBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereBetween($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function andWhereNotBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_AND;
        return $this->_whereBetween($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orWhereBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = false;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereBetween($column, $values);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orWhereNotBetween(string $column, array $values): QueryBuilder
    {// 2023-06-01
        $this->isNot = true;
        $this->boolType = self::BOOL_TYPE_OR;
        return $this->_whereBetween($column, $values);
    }

    // Helper for compiling any aggregate queries.
    private function aggregate(string $method, $column, array $options=[]): QueryBuilder
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
     * @param mixed $column
     * @param mixed $options
     */
    public function count($column=null, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('COUNT', $column ?? '*', $options);
    }
    /**
     * @param mixed $columns
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
     * @param mixed $column
     * @param mixed $options
     */
    public function min($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('MIN', $column, $options);
    }
    /**
     * @param mixed $column
     * @param mixed $options
     */
    public function max($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('MAX', $column, $options);
    }
    /**
     * @param mixed $column
     * @param mixed $options
     */
    public function sum($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('SUM', $column, $options);
    }
    /**
     * @param mixed $columns
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
     * @param mixed $column
     * @param mixed $options
     */
    public function avg($column, $options=[]): QueryBuilder
    {// 2023-05-26
        return $this->aggregate('AVG', $column, $options);
    }
    /**
     * @param mixed $columns
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
     * @param mixed $args
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

        foreach($callbacks as $callback)
        {
            $iUnion = new Union($type, $callback, $wrap ?? false);
            $this->iStatements[] = $iUnion;
        }

        return $this;
    }
    /**
     * @param mixed $args
     */
    public function union(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_BASIC, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function unionAll(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_ALL, ...$args);
    }
    /**
     * @param mixed $args
     */
    public function intersect(...$args): QueryBuilder
    {// 2023-06-02
        return $this->_union(Union::TYPE_INTERSECT, ...$args);
    }
    /**
     * @param int|Raw|QueryBuilder $value
     * @param array<int,mixed> $options
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
     */
    public function limit($value, ...$options): QueryBuilder
    {// 2023-05-26
        $this->iSingle->limit = $value;
        // this._setSkipBinding('limit', options);

        return $this;
    }

    public function clone(): QueryBuilder
    {// 2023-06-02j
        return clone $this;
    }

    public function delete()
    {// 2023-06-02

    }
}

