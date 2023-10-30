<?php

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class SharQResultGenerator implements \Iterator, \Countable
{
    protected $statement;
    protected $rowType;
    protected $currentKey = null;
    protected $currentValue;
    protected $queryBuffering;
    
    public function __construct(\PDOStatement $statement, int $rowType, bool $queryBuffering)
    {	//2018-06-07
        $this->statement      = $statement;
        $this->rowType        = $rowType;
        $this->queryBuffering = $queryBuffering;
    }
    
    protected function fetchNextRow(): void
    {	//2018-06-07
        $this->currentKey   = ($this->currentKey ?? -1) + 1;
        $this->currentValue = $this->statement->fetch($this->rowType);
        
        if ($this->currentValue === false)
        {
            $this->statement->closeCursor();
        }
    }
    
    //Iterator
    public function current()
    {	//2018-06-07
        return $this->currentValue;
    }
    
    //Iterator
    public function key()
    {	//2018-06-07
        return $this->currentKey;
    }
    
    //Iterator
    public function next(): void
    {	//2018-06-07
        $this->fetchNextRow();
    }
    
    //Iterator
    public function rewind(): void
    {	//2018-06-07
        if ($this->currentValue === false)
        {
            throw new \Exception(__CLASS__.'\'s can only be iterated once');
        }
        
        $this->fetchNextRow();
    }
    
    //Iterator
    public function valid(): bool
    {	//2018-06-07
        return ($this->currentValue !== false);
    }
    
    //Countable
    public function count(): int
    {	//2018-06-07
        if (!$this->queryBuffering)
        {
            return -1;
        }
        
        return $this->statement->rowCount();
    }
}
