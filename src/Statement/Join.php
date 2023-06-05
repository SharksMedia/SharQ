<?php

/**
 * // 2023-05-09
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder\Statement;

use Sharksmedia\QueryBuilder\Statement\IStatement;
use Sharksmedia\QueryBuilder\Statement\Raw;

class Clause
{
    public string           $type;
    public /* string */     $columnFirst;
    public                  $value;
    public /* ?string */    $operator;
    public string           $boolType;
    public bool             $isNot;

    public                  $column;

    public function getBoolFunction(): string
    {// 2023-05-31
        if($this->boolType === Join::ON_AND) return 'AND';
        if($this->boolType === Join::ON_OR) return 'OR';

        throw new \Exception('Unknown bool type: ' . $this->boolType);
    }

    public function getOnFunction(): string
    {// 2023-05-31
        if($this->type === Join::ON_TYPE_BASIC) return 'ON';
        if($this->type === Join::ON_TYPE_USING) return 'USING';
        if($this->type === Join::ON_TYPE_WRAPPED) return 'ON';

        throw new \Exception('Unknown on type: ' . $this->type);
    }

    public function getType(): string
    {// 2023-06-01
        return $this->type;
    }

    public function getColumn()
    {// 2023-05-31
        return $this->columnFirst ?? $this->column;
    }

    public function getOperator()
    {// 2023-05-31
        return $this->operator;
    }

    public function getValue()
    {// 2023-05-31
        return $this->value;
    }

    public function isNot(): bool
    {// 2023-05-31
        return $this->isNot;
    }

}

/**
 * // 2023-05-09
 * @property string $table
 * @property string $joinType
 * @property string $boolType Default bool type
 * @property string $schema
 * @property array<Clause> $iClauses
 */
class Join implements IStatement
{
    public const TYPE_RAW               = 'JOIN_RAW';
    public const TYPE_INNER             = 'JOIN_INNER';
    public const TYPE_OUTER             = 'JOIN_OUTER';
    public const TYPE_CROSS             = 'JOIN_CROSS';
    public const TYPE_LEFT              = 'JOIN_LEFT';
    public const TYPE_LEFT_OUTER        = 'JOIN_LEFT_OUTER';
    public const TYPE_RIGHT             = 'JOIN_RIGHT';
    public const TYPE_RIGHT_OUTER       = 'JOIN_RIGHT_OUTER';

    public const BOOL_AND               = 'AND';
    public const BOOL_OR                = 'OR';

    public const ON_TYPE_RAW            = 'ON_TYPE_RAW';
    public const ON_TYPE_BASIC          = 'ON_TYPE_BASIC';
    public const ON_TYPE_VALUE          = 'ON_TYPE_VALUE';
    public const ON_TYPE_BETWEEN        = 'ON_TYPE_BETWEEN';
    public const ON_TYPE_WRAPPED        = 'ON_TYPE_WRAPPED';
    public const ON_TYPE_USING          = 'ON_TYPE_USING';
    public const ON_TYPE_IN             = 'ON_TYPE_IN';
    public const ON_TYPE_NULL           = 'ON_TYPE_NULL';
    public const ON_TYPE_EXISTS         = 'ON_TYPE_EXISTS';

    public const ON_AND                 = 'ON_AND';
    public const ON_OR                  = 'ON_OR';

    private string $type;

    private /* string|Raw */ $table;
    private string $joinType;
    private string $boolType;
    private bool   $isNot;
    private ?string $schema;

    private array $iClauses = [];

    /**
     * @param string|Raw $table
     * @param string $joinType
     * @param string|null $schema
     */
    public function __construct($table, string $joinType, ?string $schema=null)
    {// 2023-05-09
        $this->table = $table;
        $this->joinType = $joinType;
        $this->schema = $schema;

        $this->boolType = self::ON_AND;
        $this->isNot = false;
    }

    public function getClass(): string
    {// 2023-05-10
        return 'Join';
    }

    public function getType(): string
    {// 2023-05-09
        return $this->joinType;
    }

    public function getTypes(): array
    {// 2023-05-09
        $types =
        [
            self::TYPE_RAW,
            self::TYPE_INNER,
            self::TYPE_OUTER,
            self::TYPE_CROSS,
            self::TYPE_LEFT,
            self::TYPE_LEFT_OUTER,
            self::TYPE_RIGHT,
            self::TYPE_RIGHT_OUTER,
        ];

        return $types;
    }

    public function getTableName(): string
    {// 2023-06-01
        return $this->table;
    }

    public function joinType(string $joinType): self
    {// 2023-05-09
        $this->joinType = $joinType;

        return $this;
    }

    public function getJoinFunction(): string
    {// 2023-05-31
        if($this->joinType === self::TYPE_INNER) return 'INNER JOIN';
        if($this->joinType === self::TYPE_OUTER) return 'OUTER JOIN';
        if($this->joinType === self::TYPE_CROSS) return 'CROSS JOIN';
        if($this->joinType === self::TYPE_LEFT) return 'LEFT JOIN';
        if($this->joinType === self::TYPE_LEFT_OUTER) return 'LEFT OUTER JOIN';
        if($this->joinType === self::TYPE_RIGHT) return 'RIGHT JOIN';
        if($this->joinType === self::TYPE_RIGHT_OUTER) return 'RIGHT OUTER JOIN';

        throw new \Exception('Unknown join type: ' . $this->joinType);
    }
    /**
     * @return Clause[]
     */
    public function getClauses(): array
    {// 2023-05-31
        return $this->iClauses;
    }

    /**
     * @param string $onType Compiler type
     * @param string $boolType
     * @param string|callable $first
     * @param string? $operator
     * @param string? $second
     * @return self
     * @param mixed $operator
     * @param mixed $second
     */
    private function getClauseFromArguments(string $onType, string $boolType, /*string|callable*/ $first=null, $operator=null, $second=null): Clause
    {// 2023-05-09
        if(is_callable($first))
        {
            $iClause = new Clause();
            $iClause->type = self::ON_TYPE_WRAPPED;
            $iClause->value = $first;
            $iClause->boolType = $boolType;

            return $iClause;
        }

        // $argCount = func_num_args();

        $args = array_filter([$onType, $boolType, $first, $operator, $second], fn($value) => $value !== null);
        $argCount = count($args);

        if($argCount === 3)
        {
            $iClause = new Clause();
            $iClause->type = self::ON_TYPE_RAW;
            $iClause->value = $first;
            $iClause->boolType = $boolType;

            return $iClause;
        }

        if($argCount === 4)
        {
            $iClause = new Clause();
            $iClause->type = $onType;
            $iClause->columnFirst = $first;
            $iClause->operator = '=';
            $iClause->value = $operator;
            $iClause->boolType = $boolType;

            return $iClause;
        }

        $iClause = new Clause();
        $iClause->type = $onType;
        $iClause->columnFirst = $first;
        $iClause->operator = $operator;
        $iClause->value = $second;
        $iClause->boolType = $boolType;

        return $iClause;
    }
    /**
     * @param mixed $first
     * @param mixed $args
     */
    public function on($first, ...$args): Join
    {// 2023-05-09
        $iClause = $this->getClauseFromArguments(self::ON_TYPE_BASIC, $this->boolType, $first, ...$args);

        $this->iClauses[] = $iClause;

        return $this;
    }
    /**
     * @param mixed $first
     * @param mixed $operator
     * @param mixed $second
     */
    public function andOn($first, $operator=null, $second=null): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;
        $this->on($first, $operator, $second);
    
        return $this;
    }
    /**
     * @param mixed $first
     * @param mixed $operator
     * @param mixed $second
     */
    public function orOn($first, $operator=null, $second=null): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        $this->on($first, $operator, $second);
    
        return $this;
    }

    public function using(string $column): self
    {// 2023-05-09
        $iClause = new Clause();
        $iClause->type = self::ON_TYPE_USING;
        $iClause->column = $column;
        $iClause->boolType = $this->boolType;

        return $this;
    }
    /**
     * @param mixed $first
     * @param mixed $args
     */
    public function onVal($first, ...$args): Join
    {// 2023-05-09
        $iClause = $this->getClauseFromArguments(self::ON_TYPE_VALUE, $this->boolType, $first, ...$args);

        $this->iClauses[] = $iClause;

        return $this;
    }
    /**
     * @param mixed $first
     * @param mixed $args
     */
    public function andOnVal($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;
        $this->onVal($first, ...$args);

        return $this;
    }
    /**
     * @param mixed $first
     * @param mixed $args
     */
    public function orOnVal($first, ...$args): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        $this->onVal($first, ...$args);

        return $this;
    }
    /**
     * @param array<int,mixed> $values
     */
    public function onBetween(string $column, array $values, bool $isNot=false): Join
    {// 2023-05-09
        if(count($values) !== 2) throw new \UnexpectedValueException('Between clause must have 2 values');
        
        $iClause = new Clause();
        $iClause->type = self::ON_TYPE_BETWEEN; 
        $iClause->column = $column;
        $iClause->value = $values;
        $iClause->boolType = $this->boolType;
        $iClause->isNot = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }
    /**
     * @param array<int,mixed> $values
     */
    public function onNotBetween(string $column, array $values): Join
    {// 2023-05-09
        return $this->onBetween($column, $values, true);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orOnBetween(string $column, array $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onBetween($column, $values, false);
    }
    /**
     * @param array<int,mixed> $values
     */
    public function orOnNotBetween(string $column, array $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onBetween($column, $values, true);
    }
    /**
     * @param mixed $values
     */
    public function onIn(string $column, $values, bool $isNot=false): Join
    {// 2023-05-09
        if(is_array($values) && count($values) === 0) return $this->on(new Raw(1), '=', new Raw(0)); // Mathes an empty array; will always be false.

        $iClause = new Clause();
        $iClause->type = self::ON_TYPE_IN;
        $iClause->column = $column;
        $iClause->value = $values;
        $iClause->boolType = $this->boolType;
        $iClause->isNot = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }
    /**
     * @param mixed $values
     */
    public function onNotIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_AND;
        return $this->onIn($column, $values, true);
    }
    /**
     * @param mixed $values
     */
    public function orOnIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onIn($column, $values, false);
    }
    /**
     * @param mixed $values
     */
    public function orOnNotIn(string $column, $values): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onIn($column, $values, true);
    }

    public function onNull(string $column, bool $isNot=false): self
    {// 2023-05-09
        $iClause = new Clause();
        $iClause->type = self::ON_TYPE_NULL;
        $iClause->column = $column;
        $iClause->boolType = $this->boolType;
        $iClause->isNot = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }

    public function onNotNull(string $column): self
    {// 2023-05-09
        return $this->onNull($column, true);
    }

    public function orOnNull(string $column): self
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onNull($column, false);
    }

    public function orOnNotNull(string $column): self
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onNull($column, true);
    }
    /**
     * @param callable(): mixed $callback
     */
    public function onExists(callable $callback, bool $isNot=false): Join
    {// 2023-05-09
        $iClause = new Clause();
        $iClause->type = self::ON_TYPE_EXISTS;
        $iClause->value = $callback;
        $iClause->boolType = $this->boolType;
        $iClause->isNot = $isNot;

        $this->iClauses[] = $iClause;

        return $this;
    }
    /**
     * @param callable(): mixed $callback
     */
    public function onNotExists(callable $callback): Join
    {// 2023-05-09
        return $this->onExists($callback, true);
    }
    /**
     * @param callable(): mixed $callback
     */
    public function orOnExists(callable $callback): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onExists($callback, false);
    }
    /**
     * @param callable(): mixed $callback
     */
    public function orOnNotExists(callable $callback): Join
    {// 2023-05-09
        $this->boolType = self::ON_OR;
        return $this->onExists($callback, true);
    }


}
