<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class TestWheres extends \Codeception\Test\Unit
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

        $cases['full sub selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                          ->from('users')
                          ->where('email', '=', 'bar');
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `email` = ? OR `id` = (SELECT MAX(id) FROM `users` WHERE `email` = ?)',
                        'bindings'=>['foo', 'bar']
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear nested selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                          ->from('users')
                          ->where('email', '=', 'bar')
                          ->clearSelect();
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `email` FROM `users` WHERE `email` = ? OR `id` = (SELECT * FROM `users` WHERE `email` = ?)',
                        'bindings'=>['foo', 'bar']
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear non nested selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                          ->from('users')
                          ->where('email', '=', 'bar');
                    })
                    ->clearSelect(),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `email` = ? OR `id` = (SELECT MAX(id) FROM `users` WHERE `email` = ?)',
                        'bindings'=>['foo', 'bar']
                    ]
                ]
            ];

            return $case;
        };

        $cases['supports arbitrarily nested raws'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('places')
                    ->where(
                        self::raw(
                            'ST_DWithin((places.address).xy, ?, ?) AND ST_Distance((places.address).xy, ?) > ? AND ?',
                            [
                                self::raw('ST_SetSRID(?,?)', [self::raw('ST_MakePoint(?,?)', [-10, 10]), 4326]),
                                100000,
                                self::raw('ST_SetSRID(?,?)', [self::raw('ST_MakePoint(?,?)', [-5, 5]), 4326]),
                                50000,
                                self::raw('places.id IN ?', [[1, 2, 3]]),
                            ]
                        )
                    ),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `places` WHERE ST_DWithin((places.address).xy, ST_SetSRID(ST_MakePoint(?,?),?), ?) AND ST_Distance((places.address).xy, ST_SetSRID(ST_MakePoint(?,?),?)) > ? AND places.id IN ?',
                        'bindings'=>[-10, 10, 4326, 100000, -5, 5, 4326, 50000, [1, 2, 3]]
                    ]
                ]
            ];

            return $case;
        };

        $cases['has a modify method which accepts a function that can modify the query'] = function()
        {
            // arbitrary number of arguments can be passed to `.modify(queryBuilder, ...)`,
            $withBars = function($queryBuilder, $table, $fk)
            {
                $queryBuilder->leftJoin('bars', $table.'.'.$fk, 'bars.id')->select('bars.*');
            };

            $case =
            [
                self::qb()
                    ->select('foo_id')
                    ->from('foos')
                    ->modify($withBars, 'foos', 'bar_id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'select `foo_id`, `bars`.* from `foos` left join `bars` on `foos`.`bar_id` = `bars`.`id`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - allows an hint comment'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('testtable')
                    ->hintComment('hint()'),
                [
                    'mysql'=>
                    [
                        'sql'=>'select /*+ hint() */ * from `testtable`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - allows multiple hint comments'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('testtable')
                    ->hintComment(['hint1()', 'hint2()'])
                    ->hintComment('hint3()'),
                [
                    'mysql'=>
                    [
                        'sql'=>'select /*+ hint1() hint2() hint3() */ * from `testtable`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1982 - should allow query comments in querybuilder'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('testtable')
                    ->comment('Added comment 1')
                    ->comment('Added comment 2'),
                [
                    'mysql'=>
                    [
                        'sql'=>'/* Added comment 1 */ /* Added comment 2 */ select * from `testtable`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['#1982 (2) - should throw error on non string'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        // $cases['#1982 (3) - should throw error when there is subcomments'] = function()
        // {
        //      // FIXME: Implement me!
        // };

        // $cases['#1982 (4) - should throw error when there is question mark'] = function()
        // {
        //       // FIXME: Implement me!
        // };

        $cases['#4199 - allows hint comments in subqueries'] = function()
        {
            $case =
            [
                self::qb()
                    ->select([
                        'c1'=>'c1',
                        'c2'=>self::qb()->select('c2')->from('t2')->hintComment('hint2()')->limit(1)
                    ])
                    ->from('t1')
                    ->hintComment('hint1()'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT /*+ hint1() */ `c1` AS `c1`, (SELECT /*+ hint2() */ `c2` FROM `t2` LIMIT ?) AS `c2` FROM `t1`',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - allows hint comments in unions'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('t1')
                    ->hintComment('hint1()')
                    ->unionAll(self::qb()->from('t2')->hintComment('hint2()')),
                [
                    'mysql'=>
                    [
                        'sql'=>'select /*+ hint1() */ * from `t1` union all select /*+ hint2() */ * from `t2`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['#4199 - forbids "/*", "*/" and "?" in hint comments'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        // $cases['#4199 - forbids non-strings as hint comments'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        foreach($cases as $name=>$caseFn)
        {
            $cases[$name] = $caseFn();
        }

        return $cases;
    }
}


