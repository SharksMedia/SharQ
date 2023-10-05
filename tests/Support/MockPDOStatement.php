<?php

declare(strict_types=1);

namespace Tests\Support;

class MockPDOStatement extends \PDOStatement
{
    protected array $results = [];

    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    public function fetchAll(int $mode = \PDO::FETCH_BOTH, $fetch_argument = null, mixed ...$args): array
    {
        return $this->results;
    }

    public function fetch(int $mode = \PDO::FETCH_BOTH, $cursor_orientation = \PDO::FETCH_ORI_NEXT, $cursor_offset = 0): mixed
    {
        return array_shift($this->results);
    }

    public function closeCursor(): bool
    {
        return true;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->results);
    }
}
