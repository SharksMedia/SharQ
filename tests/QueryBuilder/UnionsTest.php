<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class UnionsTest extends \Codeception\Test\Unit
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

    private static function qb(): QueryBuilder
    {// 2023-05-16
        $iClient = self::getClient();

        return new QueryBuilder($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['unions - simple'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where('id', '=', 1)
                    ->union(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where('id', '=', 2);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2]
                    ]
                ]
            ];

            return $case;
        };

        $cases['unions - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where(['id'=>1])
                    ->union(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['unions - argument array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where(['id'=>1])
                    ->union([
                        function($q)
                        {
                            $q->select('*')
                              ->from('users')
                              ->where(['id'=>2]);
                        },
                        function($q)
                        {
                            $q->select('*')
                              ->from('users')
                              ->where(['id'=>3]);
                        }
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps unions - basic'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where('id', 'in', function($q)
                    {
                        $q->table('users')
                          ->max('id')
                          ->union(function($q)
                          {
                              $q->table('users')
                                ->min('id');
                          }, true);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` IN((SELECT MAX(`id`) FROM `users`) UNION (SELECT MIN(`id`) FROM `users`))',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps unions - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->union(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }, true),
                [
                    'mysql'=>
                    [
                        'sql'=>'(SELECT * FROM `users` WHERE `id` = ?) UNION (SELECT * FROM `users` WHERE `id` = ?) UNION (SELECT * FROM `users` WHERE `id` = ?)',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps unions - arguments array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->union([
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }
                ], true),
                [
                    'mysql'=>
                    [
                        'sql'=>'(SELECT * FROM `users` WHERE `id` = ?) UNION (SELECT * FROM `users` WHERE `id` = ?) UNION (SELECT * FROM `users` WHERE `id` = ?)',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps union alls - basic'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where('id', 'in', function($q)
                    {
                        $q->table('users')
                          ->max('id')
                          ->unionAll(function($q)
                          {
                              $q->table('users')
                                ->min('id');
                          }, true);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` IN((SELECT MAX(`id`) FROM `users`) UNION ALL (SELECT MIN(`id`) FROM `users`))',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps union alls - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->unionAll(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }, true),
                [
                    'mysql'=>
                    [
                        'sql'=>'(SELECT * FROM `users` WHERE `id` = ?) UNION ALL (SELECT * FROM `users` WHERE `id` = ?) UNION ALL (SELECT * FROM `users` WHERE `id` = ?)',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps union alls - arguments array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->unionAll([
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }
                ], true),
                [
                    'mysql'=>
                    [
                        'sql'=>'(SELECT * FROM `users` WHERE `id` = ?) UNION ALL (SELECT * FROM `users` WHERE `id` = ?) UNION ALL (SELECT * FROM `users` WHERE `id` = ?)',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['union alls - basic'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->unionAll(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where('id', '=', 2);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2]
                    ]
                ]
            ];

            return $case;
        };

        $cases['union alls - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->unionAll(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>2]);
                    },
                    function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where(['id'=>3]);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['union alls - argument array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where(['id'=>1])
                    ->unionAll([
                        function($q)
                        {
                            $q->select('*')
                              ->from('users')
                              ->where(['id'=>2]);
                        },
                        function($q)
                        {
                            $q->select('*')
                              ->from('users')
                              ->where(['id'=>3]);
                        }
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with array of callbacks Issue #4364'] = function()
        {
            $case =
            [
                self::qb()
                    ->unionAll([
                        function($q)
                        {
                            $q->select()
                              ->from('users')
                              ->where(['id'=>1]);
                        },
                        function($q)
                        {
                            $q->select()
                              ->from('users')
                              ->where(['id'=>2]);
                        }
                    ])
                    ->first(),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? LIMIT ?',
                        'bindings'=>[1, 2, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with array of callbacks Issue #5030'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where('id', '=', 1)
                    ->groupBy('id')
                    ->unionAll(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where('id', '=', 2);
                    }, true)
                    ->first(),
                [
                    'mysql'=>
                    [
                        'sql'=>'(SELECT * FROM `users` WHERE `id` = ?) UNION ALL (SELECT * FROM `users` WHERE `id` = ?) GROUP BY `id` LIMIT ?',
                        'bindings'=>[1, 2, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple unions - basic'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->union(self::qb()->select('*')->from('users')->where('id', '=', 2))
                ->union(function($q)
                    {
                        $q->select('*')
                          ->from('users')
                          ->where('id', '=', 3);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple unions - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->union(
                    self::qb()->select('*')->from('users')->where(['id'=>2]),
                    self::raw('SELECT * FROM `users` WHERE `id` = ?', 3)
                ),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple unions - agument array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->union([
                    self::qb()->select('*')->from('users')->where(['id'=>2]),
                    self::raw('SELECT * FROM `users` WHERE `id` = ?', 3)
                ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ? UNION SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple union alls - basic'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->unionAll(self::qb()->select('*')->from('users')->where('id', '=', 2))
                ->unionAll(self::qb()->select('*')->from('users')->where('id', '=', 3)),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple union alls - multiple arguments chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->unionAll(
                    self::qb()->select('*')->from('users')->where(['id'=>2]),
                    self::raw('SELECT * FROM `users` WHERE `id` = ?', 3)
                ),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple union alls - argument array chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->where(['id'=>1])
                ->unionAll([
                    self::qb()->select('*')->from('users')->where(['id'=>2]),
                    self::raw('SELECT * FROM `users` WHERE `id` = ?', 3)
                ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ? UNION ALL SELECT * FROM `users` WHERE `id` = ?',
                        'bindings'=>[1, 2, 3]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['intersects - basic'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->select('*')
        //         ->from('users')
        //         ->where('id', '=', 1)
        //         ->intersect(function($q)
        //             {
        //                 $q->select('*')
        //                   ->from('users')
        //                   ->where('id', '=', 2);
        //             }),
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

        // $cases['wraps intersects'] = function()
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

        // $cases['multiple intersects'] = function()
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

        // $cases['excepts'] = function()
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
        // $cases['wraps excepts'] = function()
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
        // $cases['multiple excepts'] = function()
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

        foreach($cases as $name=>$caseFn)
        {
            $cases[$name] = $caseFn();
        }

        return $cases;
    }

	/**
	 * @dataProvider caseProvider
	 */
    public function testQueryBuilder(QueryBuilder $iQueryBuilder, array $iExpected)
    {
        $iQueryCompiler = new QueryCompiler(self::getClient(), $iQueryBuilder, []);

        $iQuery = $iQueryCompiler->toSQL();
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}

