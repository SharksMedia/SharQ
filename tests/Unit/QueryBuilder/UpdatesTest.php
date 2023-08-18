<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\Statement\Raw;

class UpdatesTest extends \Codeception\Test\Unit
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

        $iRaw = new Raw($query, ...$bindings);

        return $iRaw;
    }

    private static function qb(): SharQ
    {// 2023-05-16
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['update method'] = function()
        {
            $case =
            [
                self::qb()
                    ->update(['email'=>'foo', 'name'=>'bar'])
                    ->table('users')
                    ->where('id', '=', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = ?, `name` = ? WHERE `id` = ?',
                        'bindings'=>['foo', 'bar', 1]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['update only method'] = function()
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

        // $cases['should not update columns undefined values'] = function()
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

        $cases["should allow for 'null' updates"] = function()
        {
            $case =
            [
                self::qb()
                    ->update(['email'=>null, 'name'=>'bar'])
                    ->table('users')
                    ->where('id', '=', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = NULL, `name` = ? WHERE `id` = ?',
                        'bindings'=>['bar', 1]
                        // 'sql'=>'UPDATE `users` SET `email` = ?, `name` = ? WHERE `id` = ?',
                        // 'bindings'=>[null, 'bar', 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method with joins mysql'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->join('orders', 'users.id', 'orders.user_id')
                    ->where('users.id', '=', 1)
                    ->update(['email'=>'foo', 'name'=>'bar']),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` INNER JOIN `orders` ON(`users`.`id` = `orders`.`user_id`) SET `email` = ?, `name` = ? WHERE `users`.`id` = ?',
                        'bindings'=>['foo', 'bar', 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method with limit mysql'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->where('users.id', '=', 1)
                    ->update(['email'=>'foo', 'name'=>'bar'])
                    ->limit(1),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = ?, `name` = ? WHERE `users`.`id` = ? LIMIT ?',
                        'bindings'=>['foo', 'bar', 1, 1]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['update method without joins on postgres'] = function()
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
        //
        // $cases['update method with returning on oracle'] = function()
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

        $cases['update method respects raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->where('id', '=', 1)
                    ->update(['email'=>self::raw('foo'), 'name'=>'bar']),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = foo, `name` = ? WHERE `id` = ?',
                        'bindings'=>['bar', 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['increment method'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->increment('balance', 10),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` + ? WHERE `id` = ?',
                        'bindings'=>[10, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling increment multiple times on same column overwrites the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->increment('balance', 10)
                    ->increment('balance', 20),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` + ? WHERE `id` = ?',
                        'bindings'=>[20, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling increment and then decrement will overwrite the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->increment('balance', 10)
                    ->decrement('balance', 90),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` - ? WHERE `id` = ?',
                        'bindings'=>[90, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling decrement multiple times on same column overwrites the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->decrement('balance', 10)
                    ->decrement('balance', 20),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` - ? WHERE `id` = ?',
                        'bindings'=>[20, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can chain increment / decrement with .update in same build-chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->update(['email'=>'foo@bar.com'])
                    ->increment('balance', 10)
                    ->decrement('subbalance', 100),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = ?, `balance` = `balance` + ?, `subbalance` = `subbalance` - ? WHERE `id` = ?',
                        'bindings'=>['foo@bar.com', 10, 100, 1]
                    ]
                ]
            ];

            return $case;
        };

        // TODO: This test is failing because the increment/decrement is not being ignored
        // $cases['Can chain increment / decrement with .update in same build-chain and ignores increment/decrement if column is also supplied in .update'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->into('users')
        //             ->where('id', '=', 1)
        //             ->update(['balance'=>500])
        //             ->increment('balance', 10)
        //             ->decrement('balance', 100),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'UPDATE `users` SET `balance` = ? WHERE `id` = ?',
        //                 'bindings'=>[500, 1]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['Can use object syntax for increment/decrement'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->increment(['balance'=>10, 'times'=>1])
                    ->decrement(['value'=>50, 'subvalue'=>30]),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` + ?, `times` = `times` + ?, `value` = `value` - ?, `subvalue` = `subvalue` - ? WHERE `id` = ?',
                        'bindings'=>[10, 1, 50, 30, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can clear increment/decrement calls via .clearCounter()'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->update(['email'=>'foo@bar.com'])
                    ->increment(['balance'=>10])
                    ->decrement(['value'=>100])
                    ->clearCounters(),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = ? WHERE `id` = ?',
                        'bindings'=>['foo@bar.com', 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['increment method with floats'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->increment('balance', 1.23),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` + ? WHERE `id` = ?',
                        'bindings'=>[1.23, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['decrement method'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->decrement('balance', 10),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` - ? WHERE `id` = ?',
                        'bindings'=>[10, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['decrement method with floats'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->decrement('balance', 1.23),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `balance` = `balance` - ? WHERE `id` = ?',
                        'bindings'=>[1.23, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['does an update with join on mysql, #191'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('tblPerson')
                    ->update(['tblPerson.City'=>'Boonesville'])
                    ->join(
                        'tblPersonData',
                        'tblPersonData.PersonId',
                        '=',
                        'tblPerson.PersonId'
                    )
                    ->where('tblPersonData.DataId', 1)
                    ->where('tblPerson.PersonId', 5),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `tblPerson` INNER JOIN `tblPersonData` ON(`tblPersonData`.`PersonId` = `tblPerson`.`PersonId`) SET `tblPerson`.`City` = ? WHERE `tblPersonData`.`DataId` = ? AND `tblPerson`.`PersonId` = ?',
                        'bindings'=>['Boonesville', 1, 5]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, limit'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->where('id', '=', 1)
                    ->orderBy('foo', 'desc')
                    ->limit(5)
                    ->update(['email'=>'foo', 'name'=>'bar']),
                [
                    'mysql'=>
                    [
                        'sql'=>'UPDATE `users` SET `email` = ?, `name` = ? WHERE `id` = ? ORDER BY `foo` DESC LIMIT ?',
                        'bindings'=>['foo', 'bar', 1, 5]
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

	/**
	 * @dataProvider caseProvider
	 */
    public function testSharQ(SharQ $iSharQ, array $iExpected)
    {
        $iSharQCompiler = new SharQCompiler(self::getClient(), $iSharQ, []);

        $iQuery = $iSharQCompiler->toQuery('update');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
