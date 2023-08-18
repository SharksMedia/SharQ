<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class OrderByTest extends \Codeception\Test\Unit
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

        $cases['order bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy('email')
                    ->orderBy('age', 'desc'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY `email` ASC, `age` DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by array'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy(['email', 'age'=>'desc']),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY `email` ASC, `age` DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by array without order'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->orderBy([['column'=>'email'], ['column'=>'age', 'order'=>'desc']]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY `email` ASC, `age` DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by accepts query builder'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('persons')
                    ->orderBy(
                        self::qb()
                        ->select()
                        ->from('persons as p')
                        ->whereColumn('persons.id', 'p.id')
                        ->select('p.id')
                ),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `persons` ORDER BY (SELECT `p`.`id` FROM `persons` AS `p` WHERE `persons`.`id` = `p`.`id`) ASC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw order bys with default direction'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy(self::raw('col NULLS LAST')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY col NULLS LAST ASC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw order bys with specified direction'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy(self::raw('col NULLS LAST'), 'desc'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY col NULLS LAST DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['orderByRaw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderByRaw('col NULLS LAST DESC'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY col NULLS LAST DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['orderByRaw second argument is the binding'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->select('*')
        //             ->from('users')
        //             ->orderByRaw('col NULLS LAST ?', 'dEsc'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'SELECT * FROM `users` ORDER BY col NULLS LAST ?',
        //                 'bindings'=>['dEsc']
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['multiple order bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy('email')
                    ->orderBy('age', 'desc'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY `email` ASC, `age` DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, null first'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->orderBy('foo', 'desc', 'first'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY (`foo` IS NOT NULL) DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, null first, array notation'] = function()
        {
            $case =
            [
                self::qb()
                     ->select('*')
                     ->orderBy([ ['column'=>'foo', 'order'=>'desc', 'nulls'=>'first'] ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * ORDER BY (`foo` IS NOT NULL) DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, null last'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->orderBy('foo', 'desc', 'last'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY (`foo` IS NULL) DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, null last, array notation'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->orderBy([ ['column'=>'foo', 'order'=>'desc', 'nulls'=>'last'] ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY (`foo` IS NULL) DESC',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by desc'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->orderBy('email', 'desc'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` ORDER BY `email` DESC',
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

	/**
	 * @dataProvider caseProvider
	 */
    public function testSharQ(SharQ $iSharQ, array $iExpected): void
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

