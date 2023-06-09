<?php
/**
 * Class OnConflictBuilder
 * 2023-05-08
 *
 * @author      Magnus Schmidt Rasmussen <magnus@sharksmedia.dk>
 */

declare(strict_types=1);

namespace Sharksmedia\QueryBuilder;

class OnConflictBuilder
{
    /**
     * This is the QueryBuilder property.
     * @var QueryBuilder
     */
    private QueryBuilder $iQueryBuilder;

    /**
     * This is the schema property.
     * @var array<int|string, string|Raw|QueryBuilder>
     */
    private              $columns;

    /**
     * @param QueryBuilder $iQueryBuilder
     * @param array<int|string, string|Raw|QueryBuilder> $columns
     */
    public function __construct(QueryBuilder $iQueryBuilder, $columns)
    {// 2023-06-06
        $this->iQueryBuilder = $iQueryBuilder;
        $this->columns = $columns;
    }

    /**
     * @return QueryBuilder
     */
    public function ignore(): QueryBuilder
    {// 2023-06-06
        $iSingle = &$this->iQueryBuilder->getSingle();

        $iSingle->onConflict = $this->columns;
        $iSingle->ignore = true;

        return $this->iQueryBuilder;
    }

    /**
     * @param array<int, mixed> $updates
     * @return QueryBuilder
     */
    public function merge($updates=[]): QueryBuilder
    {// 2023-06-06
        $iSingle = &$this->iQueryBuilder->getSingle();

        $iSingle->onConflict = $this->columns;
        $iSingle->merge = $updates;

        return $this->iQueryBuilder;
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
