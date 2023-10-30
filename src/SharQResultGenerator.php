<?php

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class SharQResultGenerator implements \Iterator, \Countable
{
    private $stmt;
    private $fetchMode;
    private $count;
    private $currentRow;
    private $currentIndex = 0;

    public function __construct(\PDOStatement $stmt, int $fetchMode)
    {
        $this->stmt      = $stmt;
        $this->fetchMode = $fetchMode;
        $this->count     = $this->stmt->rowCount();
    }

    public function count(): int
    {
        return $this->count;
    }

    public function current()
    {
        return $this->currentRow;
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function next(): void
    {
        $this->currentIndex++;
        $this->currentRow = $this->stmt->fetch($this->fetchMode);
    }

    public function rewind(): void
    {
        throw new \BadMethodCallException("Cannot rewind a PDOStatement");
    }

    public function valid(): bool
    {
        return $this->currentRow !== false;
    }
}
