<?php

declare(strict_types=1);

namespace Sharksmedia\SharQ;

// 2023-06-14

class CustomPDO
{
    protected \PDO $pdo;

    /**
     * @var bool
     */
    private bool $isTransacting = false;

    public static function createFromPDO(\PDO $pdo): self
    {
        $iCustomPDO = new self('');

        $iCustomPDO->pdo = $pdo;

        return $iCustomPDO;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->pdo->{$name}(...$args);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->pdo->{$name};
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->pdo->{$name} = $value;
    }

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
        $this->isTransacting = $this->pdo->beginTransaction();

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

        return $this->pdo->commit();
    }
    
    /**
     * 2023-06-14
     * Rolls back a transaction
     * @return bool
     */
    public function rollback(): bool
    {
        $this->isTransacting = false;

        return $this->pdo->rollBack();
    }
}
