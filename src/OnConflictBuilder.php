<?php
/**
 * Class OnConflictBuilder
 * 2023-05-08
 *
 * @author      Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\SharQ;

class OnConflictBuilder
{
    /**
     * This is the SharQ property.
     * @var SharQ
     */
    private SharQ $iSharQ;

    /**
     * This is the schema property.
     * @var array<int|string, string|Raw|SharQ>
     */
    private              $columns;

    /**
     * @param SharQ $iSharQ
     * @param array<int|string, string|Raw|SharQ> $columns
     */
    public function __construct(SharQ $iSharQ, $columns)
    {// 2023-06-06
        $this->iSharQ = $iSharQ;
        $this->columns = $columns;
    }

    /**
     * @return SharQ
     */
    public function ignore(): SharQ
    {// 2023-06-06
        $iSingle = &$this->iSharQ->getSingle();

        $iSingle->onConflict = $this->columns;
        $iSingle->ignore = true;

        return $this->iSharQ;
    }

    /**
     * @param array<int, mixed> $updates
     * @return SharQ
     */
    public function merge($updates=[]): SharQ
    {// 2023-06-06
        $iSingle = &$this->iSharQ->getSingle();

        $iSingle->onConflict = $this->columns;
        $iSingle->merge = $updates;

        return $this->iSharQ;
    }

    /**
     * Prevent trying to call then
     * @return void
     */
    public function then(): void
    {// 2023-06-06
        throw new \Exception('Incomplete onConflict clause. .onConflict() must be directly followed by either .merge() or .ignore()');
    }
}
