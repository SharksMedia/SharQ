<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class ClearingsTest extends \Codeception\Test\Unit
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

        // 2023-05-16 ProstGreSQL only
        // $cases['selects from only'] = function() { };

        $cases['clear a select 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('id', 'email')
                    ->from('users')
                    ->clearSelect(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a select 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('id')
                    ->from('users')
                    ->clearSelect()
                    ->select('email'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `email` FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a where 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('id')
                    ->from('users')
                    ->where('id', '=', 1)
                    ->clearWhere(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `id` FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a where 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('id')
                    ->from('users')
                    ->where('id', '=', 1)
                    ->clearWhere()
                    ->where('id', '=', 2),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `id` FROM `users` WHERE `id` = ?',
                        'bindings' => [2]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a group 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->groupBy('name')
                    ->clearGroup(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a group 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->groupBy('name')
                    ->clearGroup()
                    ->groupBy('id'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users` GROUP BY `id`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear an order 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->orderBy('name', 'desc')
                    ->clearOrder(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear an order 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->orderBy('name', 'desc')
                    ->clearOrder()
                    ->orderBy('id', 'asc'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users` ORDER BY `id` ASC',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a having'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->having('id', '>', 100)
                    ->clearHaving()
                    ->having('id', '>', 10),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users` HAVING `id` > ?',
                        'bindings' => [10]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear by statements'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->with('testWith', function($queryBuilder)
                    {
                        return $queryBuilder->table('user_info')->where('a', 'b');
                    })
                    ->join('tableJoin', 'id', 'id')
                    ->select(['id'])
                    ->hintComment('hint()')
                    ->where('id', '<', 10)
                    ->groupBy('id')
                    ->groupBy('id', 'desc')
                    ->limit(100)
                    ->offset(100)
                    ->having('id', '>', 100)
                    ->union(function($q)
                    {
                        $q->select('*')->from('users')->whereNull('first_name');
                    })
                    ->unionAll(function($q)
                    {
                        $q->select('*')->from('users')->whereNull('first_name');
                    })
                    ->clear('with')
                    ->clear('join')
                    ->clear('union')
                    ->clear('columns')
                    ->select(['id'])
                    ->clear('select')
                    ->clear('hintComments')
                    ->clear('where')
                    ->clear('group')
                    ->clear('order')
                    ->clear('limit')
                    ->clear('offset')
                    ->clear('having'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can clear increment/decrement calls via .clear()'] = function()
        {
            $case =
            [
                self::qb()
                    ->into('users')
                    ->where('id', '=', 1)
                    ->update(['email' => 'foo@bar.com'])
                    ->increment(['balance' => 10])
                    ->clear('counter')
                    ->decrement(['value' => 50])
                    ->clear('counters'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'UPDATE `users` SET `email` = ? WHERE `id` = ?',
                        'bindings' => ['foo@bar.com', 1]
                    ]
                ]
            ];

            return $case;
        };

        foreach ($cases as $name => $caseFn)
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

        $iQuery         = $iSharQCompiler->toQuery();
        $sqlAndBindings =
        [
            'sql'      => $iQuery->getSQL(),
            'bindings' => $iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
