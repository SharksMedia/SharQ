<?php

declare(strict_types=1);

// 2023-01-10

namespace Sharksmedia\QueryBuilder;

/**
 * @credits: https://www.php.net/manual/en/pdo.begintransaction.php#116669
 * Commits will commit everything, I only wanted commits to actually commit when the outermost commit has been completed.  This can be done in InnoDB with savepoints.
 */
class Database extends \PDO
{
	protected $transactionCounter = 0;
	
	public function beginTransaction(): bool
	{// 2023-01-10
		$this->transactionCounter++;
		
		if($this->transactionCounter === 1) return parent::beginTransaction();
		
		$this->exec('SAVEPOINT trans'.$this->transactionCounter);
		
		return $this->transactionCounter >= 0;
	}
	
	public function commit(): bool
	{// 2023-01-10
		$this->transactionCounter--;
		
		if($this->transactionCounter === 0) return parent::commit();
		
		return $this->transactionCounter >= 0;
	}
	
	public function rollback(): bool
	{// 2023-01-10
		$this->transactionCounter--;
		
		if($this->transactionCounter === 0) return parent::rollBack();
		
		$this->exec('ROLLBACK TO trans'.($this->transactionCounter + 1));
		return true;
	}
}
