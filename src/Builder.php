<?php

/**
 * 2023-05-08
 * @author Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

class Builder
{
    /**
     * 2023-05-08
     * @return string
     */
    public function __toString(): string
    {// 2023-05-08
        return $this->toQuery();
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
        if($milliSeconds < 0) throw new \UnexpectedValueException('Timeout must be a positive integer');

        return $this;
    }
}
