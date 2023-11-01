<?php

/**
 * // 2023-05-09
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ\Statement;

use Sharksmedia\SharQ\Statement\IStatement;

/**
 * // 2023-05-09
 * @property string $sql
 * @property array<string,mixed> $bindings
 */
class Raw implements IStatement
{
    use TStatement;

    public const TYPE_RAW = 'RAW_RAW';

    private string $type;

    private string $sql;
    private array $bindings;

    private $wrapBefore = null;
    private $wrapAfter  = null;

    private bool $isNot = false;

    public function __construct($sql = null, ...$bindings)
    {// 2023-05-09
        if ($sql !== null)
        {
            $this->sql = (string)$sql;
        }

        if (count($bindings) === 1 && is_array($bindings[0]))
        {
            $bindings = $bindings[0];
        }

        $this->bindings = $bindings;
    }

    public function getClass(): string
    {// 2023-05-08
        return 'Raw';
    }
    
    public function getType(): string
    {// 2023-05-08
        return $this->type;
    }

    public function getTypes(): array
    {// 2023-05-08
        $types =
        [
            'raw',
        ];

        return $types;
    }

    public function getSQL(): string
    {// 2023-05-09
        return $this->sql;
    }

    /**
     * @return array<string,mixed>
     */
    public function getBindings(): array
    {// 2023-05-09
        return $this->bindings;
    }

    /**
     * @param string $sql
     * @param array<string,mixed> $bindings
     * @return self
     */
    public function set(string $sql, array $bindings): self
    {// 2023-05-09
        $this->sql      = $sql;
        $this->bindings = $bindings;

        return $this;
    }

    public function isWrapped(): bool
    {// 2023-05-09
        return $this->wrapBefore !== null || $this->wrapAfter !== null;
    }

    public function wrap(string $before, string $after): self
    {// 2023-05-09
        $this->wrapBefore = $before;
        $this->wrapAfter  = $after;

        return $this;
    }

    public function getWrapBefore(): ?string
    {// 2023-05-09
        return $this->wrapBefore;
    }

    public function getWrapAfter(): ?string
    {// 2023-05-09
        return $this->wrapAfter;
    }

    public function isNot(?bool $isNot = null): bool
    {// 2023-05-09
        if ($isNot !== null)
        {
            $this->isNot = $isNot;
        }

        return $this->isNot;
    }
}
