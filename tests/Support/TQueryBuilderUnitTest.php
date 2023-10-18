<?php

declare(strict_types=1);

namespace Tests\Support;

use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Statement\Raw;

trait TQueryBuilderUnitTest
{
    public static function getClient(): MySQL
    {// 2023-05-16
        $iConfig = new Config('mysql');
        $iClient = new MySQL($iConfig);

        return $iClient;
    }

    /**
     * @param string $query
     * @param mixed $bindings
     * @return Raw
     */
    public static function raw(string $query, ...$bindings): Raw
    {
        $iClient = self::getClient();

        $iRaw = new Raw($query, ...$bindings);

        return $iRaw;
    }

    private static function qb(): SharQ
    {// 2023-05-16
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }
}
