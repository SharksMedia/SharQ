<?php

namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class WithsTest extends \Codeception\Test\Unit
{
    public static function getClient(): MySQL
    {// 2023-07-31
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
    {// 2023-07-31
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-07-31
        $cases = [];

        /*

*/

        $cases['with places bindings in correct order'] = function()
        {
            $case =
            [
                self::qb()
                    ->with(
                        'updated_group',
                        self::qb()
                            ->table('group')
                            ->update(['group_name'=>'bar'])
                            ->where(['group_id'=>1])
                            ->returning('group_id')
                    )
                    ->table('user')
                    ->update(['name'=>'foo'])
                    ->where(['group_id'=>1]),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `updated_group` AS (UPDATE `group` SET `group_name` = ? WHERE `group_id` = ?) UPDATE `user` SET `name` = ? WHERE `group_id` = ?',
                        'bindings'=>['bar', 1, 'foo', 1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with delete query passed as query builder'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('delete1', function($q)
                    {
                        $q->delete()->from('accounts')->where('id', 1);
                    })
                    ->from('accounts'),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `delete1` AS (DELETE FROM `accounts` WHERE `id` = ?) SELECT * FROM `accounts`',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with delete query passed as raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('delete1', self::raw('??', self::qb()->delete()->from('accounts')->where('id', 1)))
                    ->from('accounts'),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `delete1` AS (DELETE FROM `accounts` WHERE `id` = ?) SELECT * FROM `accounts`',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with update query passed as callback'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('update1', function($q)
                    {
                        $q->from('accounts')->update(['name'=>'foo']);
                    })
                    ->from('accounts'),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `update1` AS (UPDATE `accounts` SET `name` = ?) SELECT * FROM `accounts`',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        $cases['with update query passed as query builder'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('update1', self::qb()->from('accounts')->update(['name'=>'foo']))
                    ->from('accounts'),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `update1` AS (UPDATE `accounts` SET `name` = ?) SELECT * FROM `accounts`',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        // $cases['nested and chained wrapped \'with\' clause'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //         ->withRecursive('firstWithClause', function($q)
        //         {
        //             $q->withRecursive('firstWithSubClause', function($q)
        //             {
        //                 $q->select('foo')->as('foz')->from('users');
        //             });
        //         })
        //         ->withRecursive('secondWithClause', function($q)
        //         {
        //             $q->withRecursive('secondWithSubClause', function($q)
        //                 {
        //                     $q->select('bar')->as('baz')->from('users');
        //                 })
        //             ->select('*')
        //             ->from('secondWithSubClause');
        //         })
        //         ->select('*')
        //         ->from('secondWithClause'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'WITH `update1` AS (UPDATE `accounts` SET `name` = ?) SELECT * FROM `accounts`',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['with update query passed as raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('update1', self::raw('??', self::qb()->from('accounts')->update(['name'=>'foo'])))
                    ->from('accounts'),
                [
                    'mysql'=>
                    [
                        'sql'=>'WITH `update1` AS (UPDATE `accounts` SET `name` = ?) SELECT * FROM `accounts`',
                        'bindings'=>['foo']
                    ]
                ]
            ];

            return $case;
        };

        $cases['#3 Add the "WITH RECURSIVE" functionality'] = function()
        {
            $case =
            [
                self::qb()
                    ->withRecursive('categoryTrail', ['categories_id', 'parent_id', 'level', 'trail'], function($qb)
                    {
                        $qb
                            ->select('categories_id', 'parent_id', self::raw('0 as level'), self::raw('JSON_ARRAY(categories_id) as trail'))
                            ->from('categories')
                            ->where('categories_id', '=', 2650)
                            ->unionAll(function($qb)
                            {
                                $qb
                                    ->select('c.categories_id', 'c.parent_id', self::raw('ct.level + 1'), self::raw('JSON_ARRAY_APPEND(ct.trail, "$", c.categories_id)'))
                                    ->from('categories as c')
                                    ->join('categoryTrail as ct', 'c.parent_id', '=', 'ct.categories_id');
                            });
                    })
                    ->select('*')
                    ->from('categoryTrail'),
                [
                    'mysql'=>
                    [
                        'sql'=>"WITH RECURSIVE `categoryTrail` (`categories_id`,`parent_id`,`level`,`trail`) AS (SELECT `categories_id`, `parent_id`, 0 as level, JSON_ARRAY(categories_id) as trail FROM `categories` WHERE `categories_id` = ? UNION ALL SELECT `c`.`categories_id`, `c`.`parent_id`, ct.level + 1, JSON_ARRAY_APPEND(ct.trail, \"$\", c.categories_id) FROM `categories` AS `c` INNER JOIN `categoryTrail` AS `ct` ON(`c`.`parent_id` = `ct`.`categories_id`)) SELECT * FROM `categoryTrail`",
                        'bindings'=>[2650]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#3 with chain recursive with'] = function()
        {
            $case =
            [
                self::qb()
                    ->with('categoryID', ['categories_id'], function($qb)
                    {
                        $qb->select('categories_id')
                            ->from('categories')
                            ->where('categories_id', '=', 2650);
                    })
                    ->withRecursive('categoryTrail', ['categories_id', 'parent_id', 'level', 'trail'], function($qb)
                    {
                        $qb
                            ->select('categories_id', 'parent_id', self::raw('0 as level'), self::raw('JSON_ARRAY(categories_id) as trail'))
                            ->from('categories')
                            ->whereIn('categories_id', self::qb()->select('*')->from('categoryID'))
                            ->unionAll(function($qb)
                            {
                                $qb
                                    ->select('c.categories_id', 'c.parent_id', self::raw('ct.level + 1'), self::raw('JSON_ARRAY_APPEND(ct.trail, "$", c.categories_id)'))
                                    ->from('categories as c')
                                    ->join('categoryTrail as ct', 'c.parent_id', '=', 'ct.categories_id');
                            });
                    })
                    ->select('*')
                    ->from('categoryTrail'),
                [
                    'mysql'=>
                    [
                        'sql'=>"WITH RECURSIVE `categoryID` (`categories_id`) AS (SELECT `categories_id` FROM `categories` WHERE `categories_id` = ?), `categoryTrail` (`categories_id`,`parent_id`,`level`,`trail`) AS (SELECT `categories_id`, `parent_id`, 0 as level, JSON_ARRAY(categories_id) as trail FROM `categories` WHERE `categories_id` IN(SELECT * FROM `categoryID`) UNION ALL SELECT `c`.`categories_id`, `c`.`parent_id`, ct.level + 1, JSON_ARRAY_APPEND(ct.trail, \"$\", c.categories_id) FROM `categories` AS `c` INNER JOIN `categoryTrail` AS `ct` ON(`c`.`parent_id` = `ct`.`categories_id`)) SELECT * FROM `categoryTrail`",
                        'bindings'=>[2650]
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
    public function testSharQ(SharQ $iSharQ, array $iExpected)
    {
        $iSharQCompiler = new SharQCompiler(self::getClient(), $iSharQ, []);

        $iQuery = $iSharQCompiler->toQuery('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
