<?php

namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class WheresTest extends \Codeception\Test\Unit
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

    public function _testQueryBuilder(QueryBuilder $iQueryBuilder, array $iExpected): void
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

    public function testShouldBeAbleToMakeWhereInFollowedByWhereLike(): void
    {
        $qb = self::qb()
            ->from('testtable')
            ->whereIn('id', [1])
            ->where('name', 'like', '%test%');

        // codecept_debug($qb);

        $iQueryCompiler = new QueryCompiler(self::getClient(), $qb, []);

        $iQuery = $iQueryCompiler->toQuery('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame(
        [
            'sql'=>'SELECT * FROM `testtable` WHERE `id` IN(?) AND `name` LIKE ?',
            'bindings'=>[1, '%test%']
        ], $sqlAndBindings);
    }

    public function testBasicWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ?',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testUsesWherelike2265(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereLike('name', 'luk%'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` LIKE ? COLLATE utf8_bin',
                    'bindings'=>['luk%']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testUsesWhereilike2265(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereILike('name', 'luk%'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` LIKE ?',
                    'bindings'=>['luk%']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testUsesAndwherelikeOrwherelike2265(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereLike('name', 'luk1%')
                ->andWhereLike('name', 'luk2%')
                ->orWhereLike('name', 'luk3%'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` LIKE ? COLLATE utf8_bin AND `name` LIKE ? COLLATE utf8_bin OR `name` LIKE ? COLLATE utf8_bin',
                    'bindings'=>['luk1%', 'luk2%', 'luk3%']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testUsesAndwhereilikeOrwhereilike2265(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereILike('name', 'luk1%')
                ->andWhereILike('name', 'luk2%')
                ->orWhereILike('name', 'luk3%'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` LIKE ? AND `name` LIKE ? OR `name` LIKE ?',
                    'bindings'=>['luk1%', 'luk2%', 'luk3%']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereColumn(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereColumn('users.id', '=', 'users.otherId'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `users`.`id` = `users`.`otherId`',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereNot(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNot('id', '=', 1),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE NOT `id` = ?',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testGroupedOrWhereNot(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNot(function($q)
                {
                    $q->where('id', '=', 1)
                      ->orWhereNot('id', '=', 3);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE NOT (`id` = ? OR NOT `id` = ?)',
                    'bindings'=>[1, 3]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testGroupedOrWhereNotAlternate(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(function($q)
                {
                    $q->where('id', '=', 1)
                      ->orWhereNot('id', '=', 3);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE (`id` = ? OR NOT `id` = ?)',
                    'bindings'=>[1, 3]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereNotObject(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNot(['first_name'=>'Test', 'last_name'=>'User']),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE NOT `first_name` = ? AND NOT `last_name` = ?',
                    'bindings'=>['Test', 'User']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereBool(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(true),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE 1 = 1',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereBetweens(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereBetween('id', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` BETWEEN ? AND ?',
                    'bindings'=>[1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testAndWhereBetweens(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('name', '=', 'user1')
                ->andWhereBetween('id', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` = ? AND `id` BETWEEN ? AND ?',
                    'bindings'=>['user1', 1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testAndWhereNotBetweens(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('name', '=', 'user1')
                ->andWhereNotBetween('id', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` = ? AND `id` NOT BETWEEN ? AND ?',
                    'bindings'=>['user1', 1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereBetweensAlternate(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', 'BeTween', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` BETWEEN ? AND ?',
                    'bindings'=>[1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereNotBetween(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNotBetween('id', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` NOT BETWEEN ? AND ?',
                    'bindings'=>[1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereNotBetweenAlternate(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', 'not between ', [1, 2]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` NOT BETWEEN ? AND ?',
                    'bindings'=>[1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicOrWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->orWhere('email', '=', 'foo'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `email` = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testChainedOrWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->or()
                ->where('email', '=', 'foo'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `email` = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawColumnWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(self::raw('LCASE("name")'), 'foo'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE LCASE("name") = ?',
                    'bindings'=>['foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(self::raw('id = ? or email = ?', 1, 'foo')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE id = ? or email = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawOrWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->orWhere(self::raw('email = ?', 'foo')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR email = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testChainedRawOrWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->or()
                ->where(self::raw('email = ?', 'foo')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR email = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicWhereIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IN(?, ?, ?)',
                    'bindings'=>[1, 2, 3]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testMultiColumnWhereIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn(
                    ['a', 'b'],
                    [
                        [1, 2],
                        [3, 4],
                        [5, 6],
                    ]
                ),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE (`a`, `b`) IN((?, ?), (?, ?), (?, ?))',
                    'bindings'=>[1, 2, 3, 4, 5, 6],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOrWhereIn(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->orWhereIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `id` IN(?, ?, ?)',
                    'bindings'=>[1, 1, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicWhereNotIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNotIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` NOT IN(?, ?, ?)',
                    'bindings'=>[1, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testChainedOrWhereNotIn(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->or()
                ->not()
                ->whereIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `id` NOT IN(?, ?, ?)',
                    'bindings'=>[1, 1, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOrWhereIn2(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->or()
                ->whereIn('id', [4, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `id` IN(?, ?, ?)',
                    'bindings'=>[1, 4, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testChainedBasicWhereNotIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->not()
                ->whereIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` NOT IN(?, ?, ?)',
                    'bindings'=>[1, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testChainedOrWhereNotIn2(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->or()
                ->not()
                ->whereIn('id', [1, 2, 3]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `id` NOT IN(?, ?, ?)',
                    'bindings'=>[1, 1, 2, 3],
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereInWithEmptyArray477(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn('id', []),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE 0 = 1',
                    'bindings'=>[]
                    // 'sql'=>'SELECT * FROM `users` WHERE 1 = ?',
                    // 'bindings'=>[0]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWherenotinWithEmptyArray477(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNotIn('id', []),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE 1 = 1',
                    'bindings'=>[]
                    // 'sql'=>'SELECT * FROM `users` WHERE 1 = ?',
                    // 'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testShouldAllowAFunctionAsTheFirstArgumentForAGroupedWhereClause(): void
    {
        $partial = self::qb()
            ->table('test')
            ->where('id', '=', 1);
        
        // TODO: Test partial query. mysql: 'select * from `test` where `id` = ?'

        $subWhere = function($q)
        {
            $q->where(['id'=>3])
              ->orWhere('id', 4);
        };

        $case =
        [
            $partial->where($subWhere),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `test` WHERE `id` = ? AND (`id` = ? OR `id` = ?)',
                    'bindings'=>[1, 3, 4]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testShouldAcceptAFunctionAsTheValueForASubSelect(): void
    {
        $case =
        [
            self::qb()
                ->where('id', '=', function($q)
                {
                    $q->select('account_id')
                      ->from('names')
                      ->where('names.id', '>', 1)
                      ->orWhere(function($q)
                      {
                          $q->where('names.first_name', 'like', 'Tim%')
                            ->andWhere('names.id', '>', 10);
                      });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * WHERE `id` = (SELECT `account_id` FROM `names` WHERE `names`.`id` > ? OR (`names`.`first_name` LIKE ? AND `names`.`id` > ?))',
                    'bindings'=>[1, 'Tim%', 10]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testShouldAcceptAFunctionAsTheValueForASubSelectWhenChained(): void
    {
        $case =
        [
            self::qb()
                ->where('id', '=', function($q)
                {
                    $q->select('account_id')
                      ->from('names')
                      ->where('names.id', '>', 1)
                      ->orWhere(function($q)
                      {
                          $q->where('names.first_name', 'like', 'Tim%')
                            ->andWhere('names.id', '>', 10);
                      });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * WHERE `id` = (SELECT `account_id` FROM `names` WHERE `names`.`id` > ? OR (`names`.`first_name` LIKE ? AND `names`.`id` > ?))',
                    'bindings'=>[1, 'Tim%', 10]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testShouldNotDoWherenullOnWhereFooSpaceshipNull(): void
    {
        $case =
        [
            self::qb()
                ->where('foo', '<>', null),
		    [
                'mysql'=>
                [
                    'sql'=>'SELECT * WHERE `foo` <> NULL',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testShouldExpandWhereFooNotWhereId(): void
    {
        $case =
        [
            self::qb()
                ->where('foo', '!='),
            [
                'mysql'=>
                [
                    'sql'=>"SELECT * WHERE `foo` = ?",
                    'bindings'=>['!=']
                    // 'sql'=>"SELECT * WHERE `foo` = '!='",
                    // 'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testSubSelectWhereIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn('id', function($q)
                    {
                        $q->select('id')
                          ->from('users')
                          ->where('age', '>', 25)
                          ->limit(3);
                    }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IN(SELECT `id` FROM `users` WHERE `age` > ? LIMIT ?)',
                    'bindings'=>[25, 3]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function subSelectMultiColumnWhereIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn(['id_a', 'id_b'], function($q)
                    {
                        $q->select('id_a', 'id_b')
                          ->from('users')
                          ->where('age', '>', 25)
                          ->limit(3);
                    }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE (`id_a`, `id_b`) IN(SELECT `id_a`, `id_b` FROM `users` WHERE `age` > ? LIMIT ?)',
                    'bindings'=>[25, 3]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function subSelectWhereNotIns(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNotIn('id', function($q)
                {
                    $q->select('id')
                      ->from('users')
                      ->where('age', '>', 25);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` NOT IN(SELECT `id` FROM `users` WHERE `age` > ?)',
                    'bindings'=>[25]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicWhereNulls(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNull('id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IS NULL',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicOrWhereNulls(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->orWhereNull('id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `id` IS NULL',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicWhereNotNulls(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereNotNull('id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IS NOT NULL',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testBasicOrWhereNotNulls(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '>', 1)
                ->orWhereNotNull('id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` > ? OR `id` IS NOT NULL',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereShortcut(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', 1)
                ->orWhere('name', 'foo'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR `name` = ?',
                    'bindings'=>[1, 'foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testNestedWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
            ->from('users')
            ->where('email', '=', 'foo')
            ->orWhere(function($q)
                {
                    $q->where('name', '=', 'bar')
                      ->where('age', '=', 25);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `email` = ? OR (`name` = ? AND `age` = ?)',
                    'bindings'=>['foo', 'bar', 25]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testClearNestedWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('email', '=', 'foo')
                ->orWhere(function($q)
                {
                    $q->where('name', '=', 'bar')
                      ->where('age', '=', 25)
                      ->clearWhere();
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `email` = ?',
                    'bindings'=>['foo']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testClearWhereAndNestedWheres(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('email', '=', 'foo')
                ->orWhere(function($q)
                {
                    $q->where('name', '=', 'bar')
                      ->where('age', '=', 25);
                })
                ->clearWhere(),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users`',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereExists(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('orders')
                ->whereExists(function($q)
                {
                    $q->select('*')
                      ->from('products')
                      ->where('products.id', '=', self::raw('"orders"."id"'));
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `orders` WHERE EXISTS(SELECT * FROM `products` WHERE `products`.`id` = "orders"."id")',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereExistsWithBuilder(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('orders')
                ->whereExists(
                    self::qb()->select('*')
                        ->from('products')
                        ->whereRaw('products.id = orders.id')
                ),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `orders` WHERE EXISTS(SELECT * FROM `products` WHERE products.id = orders.id)',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereNotExists(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('orders')
                ->whereNotExists(function($q)
                {
                    $q->select('*')
                      ->from('products')
                      ->where('products.id', '=', self::raw('"orders"."id"'));
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `orders` WHERE NOT EXISTS(SELECT * FROM `products` WHERE `products`.`id` = "orders"."id")',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOrWhereExists(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('orders')
                ->where('id', '=', 1)
                ->orWhereExists(function($q)
                {
                    $q->select('*')
                      ->from('products')
                      ->where('products.id', '=', self::raw('"orders"."id"'));
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `orders` WHERE `id` = ? OR EXISTS(SELECT * FROM `products` WHERE `products`.`id` = "orders"."id")',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOrWhereNotExists(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('orders')
                ->where('id', '=', 1)
                ->orWhereNotExists(function($q)
                {
                    $q->select('*')
                      ->from('products')
                      ->where('products.id', '=', self::raw('"orders"."id"'));
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `orders` WHERE `id` = ? OR NOT EXISTS(SELECT * FROM `products` WHERE `products`.`id` = "orders"."id")',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testProvidingNullOrFalseAsSecondParameterBuildsCorrectly(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('foo', null),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `foo` IS NULL',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOneAllowsPassingBuilderIntoWhereClause162(): void
    {
        $chain = self::qb()->from('chapter')->select('id')->where('book', 1);
        $page = self::qb()->from('page')->select('id')->whereIn('chapter_id', $chain);
        $word = self::qb()->from('word')->select('id')->whereIn('page_id', $page);
        $one = $word->clone()->delete();

        $case =
        [
            $one,
            [
                'mysql'=>
                [
                    'sql'=>'DELETE FROM `word` WHERE `page_id` IN(SELECT `id` FROM `page` WHERE `chapter_id` IN(SELECT `id` FROM `chapter` WHERE `book` = ?))',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testTwoAllowsPassingBuilderIntoWhereClause162(): void
    {
        $chain = self::qb()->from('chapter')->select('id')->where('book', 1);
        $page = self::qb()->from('page')->select('id')->whereIn('chapter_id', $chain);
        $word = self::qb()->from('word')->select('id')->whereIn('page_id', $page);
        $two = $page->clone()->delete();

        $case =
        [
            $two,
            [
                'mysql'=>
                [
                    'sql'=>'DELETE FROM `page` WHERE `chapter_id` IN(SELECT `id` FROM `chapter` WHERE `book` = ?)',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testThreeAllowsPassingBuilderIntoWhereClause162(): void
    {
        $chain = self::qb()->from('chapter')->select('id')->where('book', 1);
        $page = self::qb()->from('page')->select('id')->whereIn('chapter_id', $chain);
        $word = self::qb()->from('word')->select('id')->whereIn('page_id', $page);
        $three = $chain->clone()->delete();

        $case =
        [
            $three,
            [
                'mysql'=>
                [
                    'sql'=>'DELETE FROM `chapter` WHERE `book` = ?',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testSupportsCapitalizedOperators(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('name', 'LIKE', '%test%'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `name` LIKE ?',
                    'bindings'=>['%test%']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testAllowsForEmptyWhere749(): void
    {
        $case =
        [
            self::qb()
                ->select('foo')
                ->from('tbl')
                ->where(function(){}),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT `foo` FROM `tbl`',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testWhereWithDateObject(): void
    {
            $date = new \DateTime();
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where('birthday', '>=', $date),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE `birthday` >= ?',
                        'bindings'=>[$date]
                    ]
                ]
            ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawWhereWithDateObject(): void
    {
            $date = new \DateTime();
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->whereRaw('birthday >= ?', $date),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE birthday >= ?',
                        'bindings'=>[$date]
                    ]
                ]
            ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawAcceptsArrayAndNonArrayBindings1(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(self::raw('username = ?', 'knex')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE username = ?',
                    'bindings'=>['knex']
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawAcceptsArrayAndNonArrayBindings2(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where(self::raw('isadmin = ?', 0)),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE isadmin = ?',
                    'bindings'=>[0]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawAcceptsArrayAndNonArrayBindings3(): void
    {
            $date = new \DateTime();
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->where(self::raw('updtime = ?', $date)),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` WHERE updtime = ?',
                        'bindings'=>[$date]
                    ]
                ]
            ];

        $this->_testQueryBuilder(...$case);
    }

    public function testOrwheregeneratesOrAnd(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->where('id', '=', 1)
                ->orWhere([
                    'email'=>'foo',
                    'id'=>2
                ]),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` = ? OR (`email` = ? AND `id` = ?)',
                    'bindings'=>[1, 'foo', 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testNamedBindings(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn('id', self::raw('select (:test)', ['test'=>1])),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IN(select (?))',
                    'bindings'=>[1]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testMultipleNamedBindings(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->whereIn('id', self::raw('select (:test, :test2)', ['test'=>1, 'test2'=>2])),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` WHERE `id` IN(select (?, ?))',
                    'bindings'=>[1, 2]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }

    public function testRawShouldTakeNotIntoConsiderationInQuerybuilder(): void
    {
        $case =
        [
            self::qb()
                ->from('testtable')
                ->whereNot(self::raw('is_active')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `testtable` WHERE NOT is_active',
                    'bindings'=>[]
                ]
            ]
        ];

        $this->_testQueryBuilder(...$case);
    }
}

