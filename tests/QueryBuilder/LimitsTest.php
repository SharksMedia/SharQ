<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class TestLimits extends \Codeception\Test\Unit
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

        $cases['limits'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->limit(10),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT ?',
                        'bindings'=>[10]
                    ]
                ]
            ];

            return $case;
        };

        $cases['can limit 0'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->limit(0),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT ?',
                        'bindings'=>[0]
                    ]
                ]
            ];

            return $case;
        };

        $cases['limits and offsets'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->offset(5)
                    ->limit(10),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT ? OFFSET ?',
                        'bindings'=>[5, 10]
                    ]
                ]
            ];

            return $case;
        };

        $cases['limits and offsets with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->offset(self::raw('5'))
                ->limit(self::raw('10')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT 10 OFFSET 5',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['limits with skip binding'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->limit(10, ['skipBinding'=>true])
                    ->offset(5, true),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT 10 OFFSET 5',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['limits and raw selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select(self::raw('name = ? AS isJohn', ['john']))
                    ->from('users')
                    ->limit(1),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT name = ? AS isJohn FROM `users` LIMIT ?',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['first'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->first(),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT ?',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['offsets only'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->offset(5),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` LIMIT 18446744073709551615 OFFSET ?',
                        'bindings'=>[5]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not break with null call #182'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('test')
                    ->limit(null)
                    ->offset(null),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `test`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should throw warning with null call in limit'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('test')
                    ->limit(null),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `test`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should do nothing with offset when passing null'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('test')
                    ->limit(10)
                    ->offset(null),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `test` LIMIT ?',
                        'bindings'=>[10]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['should throw warning with wrong value call in offset'] = function()
        // {
        //     // FIXME:: Implement me!
        //     $case =
        //     [
        //         self::qb()
        //             ->from('test')
        //             ->limit(10)
        //             ->offset('$10'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'SELECT * FROM `test` LIMIT ?',
        //                 'bindings'=>[10]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['should clear offset when passing null'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('test')
                    ->offset(10)
                    ->offset(null),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `test`',
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
    public function testQueryBuilder(QueryBuilder $iQueryBuilder, array $iExpected)
    {
        $iQueryCompiler = new QueryCompiler(self::getClient(), $iQueryBuilder, []);

        $iQuery = $iQueryCompiler->toSQL('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}

