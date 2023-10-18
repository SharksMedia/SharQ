<?php
/**
 * Class Single
 * 2023-05-08
 *
 * @author      Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class Single
{
    /**
     * This is the schema property.
     * @var string|Raw|null
     */
    public $schema = null;

    /**
     * This is the table property.
     * @var array<int|string, string|Raw|SharQ|\Closure>|null
     */
    public ?array $table = null;

    /**
     * This is the delete property.
     * @var array<int|string, string|Raw|SharQ|\Closure>|null
     */
    public ?array $delete = null;

    /**
     * This is the columnMethod property.
     * @see Columns::TYPE_*
     * @var string|null
     */
    public ?string       $columnMethod = null;

    /**
     * This is the update property.
     * @var array<int|string, string|Raw>|null
     */
    public ?array        $update = null;

    /**
     * This is the insert property.
     * @var array<int|string, string|Raw>|null
     */
    public $insert = null;

    /**
     * This is the ignore property.
     * @var bool
     */
    public bool          $ignore = false;

    /**
     * This is the merge property.
     * @var array<int|string, string|Raw>|null
     */
    public ?array        $merge = null;

    /**
     * This is the onConflict property.
     * @var array<int|string, string|Raw>|null
     */
    public $onConflict = null;

    /**
     * This is the returning property.
     * Not used for anything yet.
     * @var string|Raw|null
     */
    public ?array        $returning = null;

    /**
     * This is the options property.
     * @var array<string, mixed>|null
     */
    public ?array        $options = null;

    /**
     * This is the counter property.
     * @var array<string, int|float>|null
     */
    public $counter = null;

    /**
     * This is the limit property.
     * @var int|Raw|null
     */
    public $limit = null;

    /**
     * This is the offset property.
     * @var int|Raw|null
     */
    public $offset = null;

    /**
     * This is the lock property.
     * SharQ::LOCK_MODE_*
     * @var string|null
     */
    public ?string       $lock = null;

    /**
     * This is the waitMode property.
     * SharQ::WAIT_MODE_*
     * @var string|null
     */
    public ?string       $waitMode = null;

    /**
     * This is the lockTables property.
     * @var array<int, string|Raw>|null
     */
    public ?array        $lockTables = null;

    /**
     * This is the alias property.
     * @var string|Raw|null
     */
    public $alias = null;

    /**
     * This is the join property.
     * @var Transaction|null
     */
    public ?Transaction  $transaction = null;
}
