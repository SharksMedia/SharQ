<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class TestDeletes extends \Codeception\Test\Unit
{
    public static function getClient()
    {// 2023-05-16
        $iConfig = new Config('mysql');
        $iClient = new MySQL($iConfig);

        return $iClient;
    }

    public static function raw(string $query, ...$bindings)
    {
        $iClient = self::getClient();

        $iRaw = new Raw($iClient);
        $iRaw->set($query, $bindings);

        return $iRaw;
    }

    private static function qb(): QueryBuilder
    {// 2023-05-16
        $iClient = self::getClient();

        return new QueryBuilder($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['delete method'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->delete(),
                [
                    'mysql'=>
                    [
                        'sql'=>'DELETE FROM `users` WHERE `email` = ?',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        // $cases['delete only method'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->select('*')
        //             ->from('my_table'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'SELECT * FROM `my_schema`.`my_table`',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['truncate method'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->truncate(),
                [
                    'mysql'=>
                    [
                        'sql'=>'TRUNCATE `users`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        foreach($cases as $name=>$caseFn)
        {
            $cases[$name] = $caseFn();
        }

        return $cases;
    }
}
