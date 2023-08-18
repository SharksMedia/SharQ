<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class InsertsTest extends \Codeception\Test\Unit
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

        $cases['insert method'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->insert(['email'=>'foo']),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`) VALUES (?)',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        ['email'=>'foo', 'name'=>'taylor'],
                        ['email'=>'bar', 'name'=>'dayle'],
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (?, ?)',
                        'bindings'=>['foo', 'taylor', 'bar', 'dayle']
                    ]
                ]
            ];

            return $case;
        };

        // TODO: Implement nullAsDefault: true
        // $cases['multiple inserts with partly undefined keys client with configuration nullAsDefault: true'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->from('users')
        //             ->insert([
        //                 ['email'=>'foo', 'name'=>'taylor'],
        //                 ['name'=>'dayle'],
        //             ]),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (NULL, ?)',
        //                 'bindings'=>['foo', 'taylor', 'dayle']
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['multiple inserts with partly undefined keys'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        ['email'=>'foo', 'name'=>'taylor'],
                        ['name'=>'dayle'],
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (DEFAULT, ?)',
                        'bindings'=>['foo', 'taylor', 'dayle']
                    ]
                ]
            ];

            return $case;
        };

        // $cases['multiple inserts with partly undefined keys throw error with sqlite'] = function()
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

        $cases['multiple inserts with returning'] = function()
        {
            // returning only supported directly by postgres and with workaround with oracle
            // other databases implicitly return the inserted id
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        ['email'=>'foo', 'name'=>'taylor'],
                        ['email'=>'bar', 'name'=>'dayle'],
                    ], ['id']),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (?, ?)',
                        'bindings'=>['foo', 'taylor', 'bar', 'dayle']
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with multiple returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        ['email'=>'foo', 'name'=>'taylor'],
                        ['email'=>'bar', 'name'=>'dayle'],
                    ], ['id', 'name']),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (?, ?)',
                        'bindings'=>['foo', 'taylor', 'bar', 'dayle']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert method respects raw bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert(['email'=>self::raw('CURRENT TIMESTAMP')])
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`) VALUES (CURRENT TIMESTAMP)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['normalizes for missing keys in insert'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert([
                        [ 'a' => 1 ],
                        [ 'b' => 2 ],
                        [ 'a' => 2, 'c' => 3 ]
                    ])
                    ->into('table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `table` (`a`, `b`, `c`) VALUES (?, DEFAULT, DEFAULT), (DEFAULT, ?, DEFAULT), (?, DEFAULT, ?)',
                        'bindings'=>[1, 2, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        // 2023-06-07 We throw an exception instead
        // $cases['empty insert should be a noop'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->into('users')
        //             ->insert(),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        // 2023-06-07 We throw an exception instead
        // $cases['insert with empty array should be a noop'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->into('users')
        //             ->insert([]),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        // $cases['insert with array with empty object and returning'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->into('users')
        //             ->insert([[]], ['id']),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'INSERT INTO `users` () VALUES ()',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['insert method respects raw bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert(['email'=>self::raw('CURRENT TIMESTAMP')])
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`) VALUES (CURRENT TIMESTAMP)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert([ 'email' => 'foo' ])
                    ->onConflict('email')
                    ->ignore()
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT IGNORE INTO `users` (`email`) VALUES (?)',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore multiple'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert([ [ 'email' => 'foo' ], [ 'email' => 'bar' ] ])
                    ->onConflict('email')
                    ->ignore()
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT IGNORE INTO `users` (`email`) VALUES (?), (?)',
                        'bindings'=>['foo', 'bar']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore multiple with raw onConflict'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert([ [ 'email' => 'foo' ], [ 'email' => 'bar' ] ])
                    ->onConflict(self::raw('(value) WHERE deleted_at IS NULL'))
                    ->ignore()
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT IGNORE INTO `users` (`email`) VALUES (?), (?)',
                        'bindings'=>['foo', 'bar']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore with composite unique keys'] = function()
        {
            $case =
            [
                self::qb()
                    ->insert([ [ 'org' => 'acme-inc', 'email' => 'foo' ] ])
                    ->onConflict(['org', 'email'])
                    ->ignore()
                    ->into('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT IGNORE INTO `users` (`email`, `org`) VALUES (?, ?)',
                        'bindings'=>['foo', 'acme-inc']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert merge with explicit updates'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        [ 'email' => 'foo', 'name' => 'taylor' ],
                        [ 'email' => 'bar', 'name' => 'dayle' ]
                    ])
                    ->onConflict('email')
                    ->merge([ 'name' => 'overidden' ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (?, ?) ON DUPLICATE KEY UPDATE `name` = ?',
                        'bindings'=>['foo', 'taylor', 'bar', 'dayle', 'overidden']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert merge multiple with implicit updates'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert([
                        [ 'email' => 'foo', 'name' => 'taylor' ],
                        [ 'email' => 'bar', 'name' => 'dayle' ]
                    ])
                    ->onConflict('email')
                    ->merge(),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`, `name`) VALUES (?, ?), (?, ?) ON DUPLICATE KEY UPDATE `email` = VALUES(`email`), `name` = VALUES(`name`)',
                        'bindings'=>['foo', 'taylor', 'bar', 'dayle']
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert get id'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->insert(['email'=>'foo'], ['id']),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `users` (`email`) VALUES (?)',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows insert values of sub-select, #121'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('entries')
                    ->insert([
                        'secret'=>123,
                        'sequence'=>self::qb()->count('*')->from('entries')->where('secret', 123)
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `entries` (`secret`, `sequence`) VALUES (?, (SELECT COUNT(*) FROM `entries` WHERE `secret` = ?))',
                        'bindings'=>[123, 123]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows specifying the columns and the query for insert, #211'] = function()
        {
            $case =
            [
                self::qb()
                    ->into(self::raw('recipients (recipient_id, email)'))
                    ->insert(
                        self::qb()
                            ->select(self::raw('?, ?', 1, 'foo@bar.com'))
                            ->whereNotExists(function($q)
                            {
                                $q->select(self::raw(1))->from('recipients')->where('recipient_id', 1);
                            })
                    ),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO recipients (recipient_id, email) SELECT ?, ? WHERE NOT EXISTS(SELECT 1 FROM `recipients` WHERE `recipient_id` = ?)',
                        'bindings'=>[1, 'foo@bar.com', 1]
                    ]
                ]
            ];

            return $case;
        };

        // 2023-06-07 Don't think we should support this. It's too crazy. And a missuse of raw
        // $cases['does crazy advanced inserts with clever raw use, #211'] = function()
        // {
        //     $q1 = self::qb()
        //             ->select(self::raw("'user'"), self::raw("'user@foo.com'"))
        //             ->whereNotExists(function($q)
        //             {
        //                 $q->select(1)->from('recipients')->where('recipient_id', 1);
        //             });
        //
        //     $q2 = self::qb()
        //             ->table('recipients')
        //             ->insert(self::raw('(recipient_id, email) ?', $q1));
        //
        //     $case =
        //     [
        //         $q2,
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'INSERT INTO `recipients` (recipient_id, email) SELECT \'user\', \'user@foo.com\' WHERE NOT EXISTS (SELECT 1 FROM `recipients` WHERE `recipient_id` = ?)',
        //                 'bindings'=>[1]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };
        
        // $cases['insert merge with where clause'] = function()
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

        // $cases['throws if you try to use an invalid operator in an inserted statement'] = function()
        // {
        //      // FIXME: Implement me!
        // };

        $cases['allows sub-query function on insert, #427 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('votes')
                    ->insert(function($q)
                    {
                        $q->select('*')->from('votes')->where('id', 99);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `votes` SELECT * FROM `votes` WHERE `id` = ?',
                        'bindings'=>[99]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows sub-query chain on insert, #427 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('votes')
                    ->insert(self::qb()->select('*')->from('votes')->where('id', 99)),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `votes` SELECT * FROM `votes` WHERE `id` = ?',
                        'bindings'=>[99]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows insert values of sub-select without raw, #627'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('entries')
                    ->insert([
                        'secret'=>123,
                        'sequence'=>self::qb()->count('*')->from('entries')->where('secret', 123)
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'INSERT INTO `entries` (`secret`, `sequence`) VALUES (?, (SELECT COUNT(*) FROM `entries` WHERE `secret` = ?))',
                        'bindings'=>[123, 123]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['#1268 - valueForUndefined should be in toSQL(SharQCompiler)'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->insert([
        //                 ['id'=>null, 'name'=>'test', 'occupation'=>null],
        //                 ['id'=>1, 'name'=>null, 'occupation'=>'none']
        //             ])
        //             ->into('users'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'INSERT INTO `users` (`id`, `name`, `occupation`) VALUES (DEFAULT, ?, DEFAULT), (?, DEFAULT, ?)',
        //                 'bindings'=>['test', 1, 'none']
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

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

        $iQuery = $iSharQCompiler->toQuery();
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
