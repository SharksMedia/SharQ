<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class HavingsTest extends \Codeception\Test\Unit
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

        $cases['havings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->having('email', '>', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `email` > ?',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->having('baz', '>', 5)
                ->orHaving('email', '>', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` > ? OR `email` > ?',
                        'bindings'=>[5, 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['nested having'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->having(function($q)
                    {
                        $q->where('email', '>', 1);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING (`email` > ?)',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['nested or havings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->having(function($q)
                    {
                        $q->where('email', '>', 10);
                        $q->orWhere('email', '=', 7);
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING (`email` > ? OR `email` = ?)',
                        'bindings'=>[10, 7]
                    ]
                ]
            ];

            return $case;
        };

        $cases['grouped having'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->groupBy('email')
                    ->having('email', '>', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` GROUP BY `email` HAVING `email` > ?',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having from'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('email as foo_email')
                    ->from('users')
                    ->having('foo_email', '>', 1),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `email` AS `foo_email` FROM `users` HAVING `foo_email` > ?',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw havings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->having(self::raw('user_foo < user_bar')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING user_foo < user_bar',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw or havings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->having('baz', '=', 1)
                    ->orHaving(self::raw('user_foo < user_bar')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` = ? OR user_foo < user_bar',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNull('baz'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IS NULL',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNull('baz')
                    ->orHavingNull('foo'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IS NULL OR `foo` IS NULL',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having not null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotNull('baz'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IS NOT NULL',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having not null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotNull('baz')
                    ->orHavingNotNull('foo'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IS NOT NULL OR `foo` IS NOT NULL',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingExists(function($q)
                    {
                        $q
                          ->select('baz')
                          ->from('users');
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING EXISTS (SELECT `baz` FROM `users`)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingExists(function($q)
                    {
                        $q->select('baz')
                          ->from('users');
                    })
                    ->orHavingExists(function($q)
                    {
                        $q->select('foo')
                          ->from('users');
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING EXISTS (SELECT `baz` FROM `users`) OR EXISTS (SELECT `foo` FROM `users`)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having not exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->havingNotExists(function($q)
                    {
                        $q->select('baz')
                          ->from('users');
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING NOT EXISTS (SELECT `baz` FROM `users`)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having not exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotExists(function($q)
                    {
                        $q->select('baz')
                          ->from('users');
                    })
                    ->orHavingNotExists(function($q)
                    {
                        $q->select('foo')
                          ->from('users');
                    }),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING NOT EXISTS (SELECT `baz` FROM `users`) OR NOT EXISTS (SELECT `foo` FROM `users`)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingBetween('baz', [5, 10]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` BETWEEN ? AND ?',
                        'bindings'=>[5, 10]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->havingBetween('baz', [5, 10])
                ->orHavingBetween('baz', [20, 30]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` BETWEEN ? AND ? OR `baz` BETWEEN ? AND ?',
                        'bindings'=>[5, 10, 20, 30]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having not between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotBetween('baz', [5, 10]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` NOT BETWEEN ? AND ?',
                        'bindings'=>[5 ,10]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having not between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotBetween('baz', [5, 10])
                    ->orHavingNotBetween('baz', [20, 30]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` NOT BETWEEN ? AND ? OR `baz` NOT BETWEEN ? AND ?',
                        'bindings'=>[5, 10, 20, 30]
                    ]
                ]
            ];

            return $case;
        };

        $cases['having in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->havingIn('baz', [5, 10, 37]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IN (?, ?, ?)',
                        'bindings'=>[5, 10, 37]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                ->from('users')
                ->havingIn('baz', [5, 10, 37])
                ->orHavingIn('foo', ['Batman', 'Joker']),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` IN (?, ?, ?) OR `foo` IN (?, ?)',
                        'bindings'=>[5, 10, 37, 'Batman', 'Joker']
                    ]
                ]
            ];

            return $case;
        };

        $cases['having not in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotIn('baz', [5, 10, 37]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` NOT IN (?, ?, ?)',
                        'bindings'=>[5, 10, 37]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or having not in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->havingNotIn('baz', [5, 10, 37])
                    ->orHavingNotIn('foo', ['Batman', 'Joker']),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` HAVING `baz` NOT IN (?, ?, ?) OR `foo` NOT IN (?, ?)',
                        'bindings'=>[5, 10, 37, 'Batman', 'Joker']
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

        $iQuery = $iQueryCompiler->toQuery('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}

