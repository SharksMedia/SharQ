<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

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

    private static function qb(): SharQ
    {// 2023-05-16
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }

	/**
	 * @dataProvider caseProvider
	 */
    public function _testSharQ(SharQ $iSharQ, array $iExpected)
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

    public function unionsSimple()
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

        $this->_testSharQ(...$case);
    }

    public function unionsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function unionsArgumentArrayChain()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionsBasic()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionsArgumentsArrayChain()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionAllsBasic()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionAllsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function wrapsUnionAllsArgumentsArrayChain()
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

        $this->_testSharQ(...$case);
    }

    public function unionAllsBasic()
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

        $this->_testSharQ(...$case);
    }

    public function unionAllsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function unionAllsArgumentArrayChain()
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

        $this->_testSharQ(...$case);
    }

    public function withArrayOfCallbacksIssue4364()
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

        $this->_testSharQ(...$case);
    }

    public function withArrayOfCallbacksIssue5030()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionsBasic()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionsAgumentArrayChain()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionAllsBasic()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionAllsMultipleArgumentsChain()
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

        $this->_testSharQ(...$case);
    }

    public function multipleUnionAllsArgumentArrayChain()
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

        $this->_testSharQ(...$case);
    }
}

