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

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['basic wheres'] = function()
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

            return $case;
        };

        $cases['uses whereLike, #2265'] = function()
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

            return $case;
        };

        $cases['uses whereILike, #2265'] = function()
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

            return $case;
        };

        $cases['uses andWhereLike, orWhereLike #2265'] = function()
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

            return $case;
        };

        $cases['uses andWhereILike, orWhereILike #2265'] = function()
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

            return $case;
        };

        $cases['where column'] = function()
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

            return $case;
        };

        $cases['where not'] = function()
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

            return $case;
        };

        $cases['grouped or where not'] = function()
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

            return $case;
        };

        $cases['grouped or where not alternate'] = function()
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

            return $case;
        };

        $cases['where not object'] = function()
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

            return $case;
        };

        // $cases['where not should throw warning when used with "in" or "between"'] = function()
        // {
            // TODO: This should throw a warning
            //
            //   it('where not should throw warning when used with "in" or "between"', function () {
              //   try {
              //     clientsWithCustomLoggerForTestWarnings.pg
              //       .queryBuilder()
              //       .select('*')
              //       .from('users')
              //       .whereNot('id', 'in', [1, 2, 3]);
              //     throw new Error('Should not reach this point');
              //   } catch (error) {
              //     expect(error.message).to.equal(
              //       'whereNot is not suitable for "in" and "between" type subqueries. You should use "not in" and "not between" instead.'
              //     );
              //   }
              //
              //   try {
              //     clientsWithCustomLoggerForTestWarnings.pg
              //       .queryBuilder()
              //       .select('*')
              //       .from('users')
              //       .whereNot('id', 'between', [1, 3]);
              //     throw new Error('Should not reach this point');
              //   } catch (error) {
              //     expect(error.message).to.equal(
              //       'whereNot is not suitable for "in" and "between" type subqueries. You should use "not in" and "not between" instead.'
              //     );
              //   }
              // });

        // };

        // $cases['where not should not throw warning when used with "in" or "between" as equality'] = function()
        // {
            // TODO: This should throw a warning
            //
              // it('where not should not throw warning when used with "in" or "between" as equality', function () {
              //   testquery(
              //     clientsWithCustomLoggerForTestWarnings.pg
              //       .queryBuilder()
              //       .select('*')
              //       .from('users')
              //       .whereNot('id', 'in'),
              //     {
              //       mysql: "select * from `users` where not `id` = 'in'",
              //       pg: 'select * from "users" where not "id" = \'in\'',
              //       'pg-redshift': 'select * from "users" where not "id" = \'in\'',
              //       mssql: "select * from [users] where not [id] = 'in'",
              //     }
              //   );
              //
              //   testquery(
              //     clientsWithCustomLoggerForTestWarnings.pg
              //       .queryBuilder()
              //       .select('*')
              //       .from('users')
              //       .whereNot('id', 'between'),
              //     {
              //       mysql: "select * from `users` where not `id` = 'between'",
              //       pg: 'select * from "users" where not "id" = \'between\'',
              //       'pg-redshift': 'select * from "users" where not "id" = \'between\'',
              //       mssql: "select * from [users] where not [id] = 'between'",
              //     }
              //   );
              // });
              //
        // };

        $cases['where bool'] = function()
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

            return $case;
        };

        $cases['where betweens'] = function()
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

            return $case;
        };

        $cases['and where betweens'] = function()
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

            return $case;
        };

        $cases['and where not betweens'] = function()
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

            return $case;
        };

        $cases['where betweens, alternate'] = function()
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

            return $case;
        };

        $cases['where not between'] = function()
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

            return $case;
        };

        $cases['where not between, alternate'] = function()
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

            return $case;
        };

        $cases['basic or wheres'] = function()
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

            return $case;
        };

        $cases['chained or wheres'] = function()
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

            return $case;
        };

        $cases['raw column wheres'] = function()
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

            return $case;
        };

        $cases['raw wheres'] = function()
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

            return $case;
        };

        $cases['raw or wheres'] = function()
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

            return $case;
        };

        $cases['chained raw or wheres'] = function()
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

            return $case;
        };

        $cases['basic where ins'] = function()
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

            return $case;
        };

        $cases['multi column where ins'] = function()
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

            return $case;
        };

        $cases['orWhereIn'] = function()
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

            return $case;
        };

        $cases['basic where not ins'] = function()
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

            return $case;
        };

        $cases['chained or where not in'] = function()
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

            return $case;
        };

        $cases['or.whereIn'] = function()
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

            return $case;
        };

        $cases['chained basic where not ins'] = function()
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

            return $case;
        };

        $cases['chained or where not in'] = function()
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

            return $case;
        };

        $cases['where in with empty array, #477'] = function()
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

            return $case;
        };

        $cases['whereNotIn with empty array, #477'] = function()
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

            return $case;
        };

        $cases['should allow a function as the first argument, for a grouped where clause'] = function()
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

            return $case;
        };

        $cases['should accept a function as the "value", for a sub select'] = function()
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

            return $case;
        };

        $cases['should accept a function as the "value", for a sub select when chained'] = function()
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

            return $case;
        };

        $cases['should not do whereNull on where("foo", "<>", null) #76'] = function()
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

            return $case;
        };

        $cases['should expand where("foo", "!=") to - where id = "!="'] = function()
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

            return $case;
        };

        $cases['sub select where ins'] = function()
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

            return $case;
        };

        $cases['sub select multi column where ins'] = function()
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

            return $case;
        };

        $cases['sub select where not ins'] = function()
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

            return $case;
        };

        $cases['basic where nulls'] = function()
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

            return $case;
        };

        $cases['basic or where nulls'] = function()
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

            return $case;
        };

        $cases['basic where not nulls'] = function()
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

            return $case;
        };

        $cases['basic or where not nulls'] = function()
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

            return $case;
        };

        $cases['where shortcut'] = function()
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

            return $case;
        };

        $cases['nested wheres'] = function()
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

            return $case;
        };

        $cases['clear nested wheres'] = function()
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

            return $case;
        };

        $cases['clear where and nested wheres'] = function()
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

            return $case;
        };

        $cases['where exists'] = function()
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

            return $case;
        };

        $cases['where exists with builder'] = function()
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

            return $case;
        };

        $cases['where not exists'] = function()
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

            return $case;
        };

        $cases['or where exists'] = function()
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

            return $case;
        };

        $cases['or where not exists'] = function()
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

            return $case;
        };

        $cases['providing null or false as second parameter builds correctly'] = function()
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

            return $case;
        };

        $chain = self::qb()->from('chapter')->select('id')->where('book', 1);
        $page = self::qb()->from('page')->select('id')->whereIn('chapter_id', $chain);
        $word = self::qb()->from('word')->select('id')->whereIn('page_id', $page);
        $three = $chain->clone()->delete();
        $two = $page->clone()->delete();
        $one = $word->clone()->delete();

        $cases['[one] allows passing builder into where clause, #162'] = function() use($one)
        {
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

            return $case;
        };

        $cases['[two] allows passing builder into where clause, #162'] = function() use($two)
        {
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

            return $case;
        };
        $cases['[three] allows passing builder into where clause, #162'] = function() use($three)
        {
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

            return $case;
        };

        $cases['supports capitalized operators'] = function()
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

            return $case;
        };

        // $cases['supports POSIX regex operators in Postgres'] = function()
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

        // $cases['supports NOT ILIKE operator in Postgres'] = function()
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

        // $cases['throws if you try to use an invalid operator'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        $cases['Allows for empty where #749'] = function()
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

            return $case;
        };

        $cases['where with date object'] = function()
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

            return $case;
        };

        $cases['raw where with date object'] = function()
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

            return $case;
        };

        $cases['#965 - .raw accepts Array and Non-Array bindings 1'] = function()
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

            return $case;
        };

        $cases['#965 - .raw accepts Array and Non-Array bindings 2'] = function()
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

            return $case;
        };

        $cases['#965 - .raw accepts Array and Non-Array bindings 3'] = function()
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

            return $case;
        };

        $cases['#1118 orWhere({..}) generates or (and - and - and)'] = function()
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
            ]; return $case;
        };

        $cases['#1228 Named bindings'] = function()
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

            return $case;
        };

        $cases['Multiple named bindings'] = function()
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

            return $case;
        };

        $cases['#1402 - raw should take "not" into consideration in querybuilder'] = function()
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
    public function testQueryBuilder(QueryBuilder $iQueryBuilder, array $iExpected): void
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
            'bindings'=>[]
        ], $sqlAndBindings);
    }
}

