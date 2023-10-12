<?php

declare(strict_types=1);

namespace Sharksmedia\SharQ;

// 2023-06-14

class CustomPDO extends \PDO
{
    /**
     * @var bool
     */
    private bool $isTransacting = false;

    /**
     * 2023-06-14
     * Gets wether the client is currently transacting.
     * @return bool
     */
    public function isTransacting(): bool
    {
        return $this->isTransacting;
    }
    
    /**
     * 2023-06-14
     * Initiates a transaction
     * @return bool
     */
    public function beginTransaction(): bool
    {
        $this->isTransacting = parent::beginTransaction();

        return $this->isTransacting;
    }
    
    /**
     * 2023-06-14
     * Commits a transaction
     * @return bool
     */
    public function commit(): bool
    {
        $this->isTransacting = false;

        return parent::commit();
    }
    
    /**
     * 2023-06-14
     * Rolls back a transaction
     * @return bool
     */
    public function rollback(): bool
    {
        $this->isTransacting = false;

        return parent::rollBack();
    }
}


