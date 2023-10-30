<?php

declare(strict_types=1);

class SharQResultGenerator implements Iterator, Countable
{
    private $stmt;
    private $count;
    private $currentRow;
    private $currentIndex = 0;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt  = $stmt;
        $this->count = $this->stmt->rowCount();
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
        $this->currentRow = $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rewind(): void
    {
        throw new BadMethodCallException("Cannot rewind a PDOStatement");
    }

    public function valid(): bool
    {
        return $this->currentRow !== false;
    }
}
