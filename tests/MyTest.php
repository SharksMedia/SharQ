<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class MyTest extends \Codeception\Test\Unit
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

    // tests
    public function testSomeFeature()
    {

    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];
        /*
        * Query Context
        $cases['should use custom wrapper on multiple inserts with returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should use custom wrapper'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should use custom wrapper on multiple inserts with returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should use custom wrapper on multiple inserts with multiple returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should pass the query context to the custom wrapper'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should pass the query context for raw queries'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should allow chaining'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should return the query context if called with no arguments'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should copy the query context'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not modify the original query context if the clone is modified'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should only shallow clone the query context'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };
        */

        /*   Query builder start     */
        $cases['basic select'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users'),
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

        $cases['adding selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select distinct'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with alias as property-value pairs'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with mixed pure column and alias pair'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with array-wrapped alias pair'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with mixed pure column and alias pair'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic old-style alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic alias trims spaces'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows for case-insensitive alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows alias with dots in the identifier name'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['less trivial case of object alias syntax'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic table wrapping'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic table wrapping with declared schema'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['selects from only'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a select'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a where'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear a group'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['clear an order'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic wheres'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['whereColumn'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where not should throw warning when used with "in" or "between"'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where not should not throw warning when used with "in" or "between" as equality'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where bool'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['whereIn with empty array, #477'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should allow a function as the first argument, for a grouped where clause'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['unions'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps unions'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps union alls'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['union alls'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple unions'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple union alls'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['intersects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps intersects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple intersects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['excepts'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wraps excepts'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple excepts'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['group bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw group bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['orderByRaw second argument is the binding'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple order bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['havings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['limits'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['full sub selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['cross join'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['full outer join'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['cross join on'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic joins'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['right (outer) joins'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['complex join'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['complex join with nest conditional statements'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['complex join with empty in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['joins with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['joins with schema'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on not null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on not null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on not exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on not exists'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on not between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on not between'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on in with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on not in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['or on not in'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['on json path join'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw expressions in select'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count with string alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count with object alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct with string alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct with object alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct with multiple columns'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['count distinct with multiple columns with alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['max'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['max with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['min'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['min with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['sum'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['sum with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['sum distinct'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['sum distinct with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['avg'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['avg with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['avg distinct with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with partly undefined keys client with configuration nullAsDefault: true'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with partly undefined keys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with partly undefined keys throw error with sqlite'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['multiple inserts with multiple returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert method respects raw bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['normalizes for missing keys in insert'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['empty insert should be a noop'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert with empty array should be a noop'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert with array with empty object and returning'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update only method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not update columns undefined values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['order by, limit'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method with joins mysql'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method with limit mysql'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method without joins on postgres'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method with returning on oracle'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['update method respects raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['increment method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling increment multiple times on same column overwrites the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling increment and then decrement will overwrite the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling decrement multiple times on same column overwrites the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert method respects raw bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore multiple'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore multiple with raw onConflict'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert ignore with composite unique keys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert merge with explicit updates'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert merge multiple with implicit updates'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert merge with where clause'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Calling decrement and then increment will overwrite the previous value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can chain increment / decrement with .update in same build-chain'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can chain increment / decrement with .update in same build-chain and ignores increment/decrement if column is also supplied in .update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can use object syntax for increment/decrement'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can clear increment/decrement calls via .clearCounter()'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['increment method with floats'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['decrement method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['decrement method with floats'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['delete method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['delete only method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['truncate method'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['insert get id'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['wrapping'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock for update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock in share mode'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock for no key update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock for key share'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should allow lock (such as forUpdate) outside of a transaction'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock only some tables for update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock only some tables for update (with array #4878)'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock for update with skip locked #1937'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['lock for update with nowait #1937'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['noWait and skipLocked require a lock mode to be set'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['skipLocked conflicts with noWait and vice-versa'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows insert values of sub-select, #121'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows left outer join with raw values'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should throw warning with wrong value call in offset'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should clear offset when passing null'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        // FIXME: You are here
        $cases['allows passing builder into where clause, #162'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows specifying the columns and the query for insert, #211'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['does an update with join on mysql, #191'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['does crazy advanced inserts with clever raw use, #211'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['supports POSIX regex operators in Postgres'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['supports NOT ILIKE operator in Postgres'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['throws if you try to use an invalid operator'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['throws if you try to use an invalid operator in an inserted statement'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#287 - wraps correctly for arrays'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows wrap on raw to wrap in parens and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows select as syntax'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows function for subselect column'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows first as syntax'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['has joinRaw for arbitrary join clauses'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows a raw query in the second param'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows join "using"'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows sub-query function on insert, #427'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with string and no partition'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with array and no partition'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with array'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with string'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with array and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with string and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with function'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with function and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with function and arrays'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with function and chains'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['chained dense_rank '] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['dense_rank with raw and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with string and no partition'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with array and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with array'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with string'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with array and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with string and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with function'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with function and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with function and arrays'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with function and chains'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['chained rank'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['rank with raw and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with string and no partition'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with array and no partition'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with array'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with object'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with string'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with array and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with string and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with function'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with function and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with function and arrays'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with function and chains'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['chained row_number'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['row_number with raw and alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows sub-query function on insert, #427'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows sub-query chain on insert, #427'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows for raw values in join, #441'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows insert values of sub-select without raw, #627'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should always wrap subquery with parenthesis'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['correctly orders parameters when selecting from subqueries, #704'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['escapes queries properly, #737'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['has a fromJS method for json construction of queries'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['uses fromRaw api, #1767'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['has a modify method which accepts a function that can modify the query'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Allows for empty where #749'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['escapes single quotes properly'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['escapes double quotes property'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['escapes backslashes properly'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows join without operator and with value 0 #953'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['allows join with operator and with value 0 #953'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where with date object'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw where with date object'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#965 - .raw accepts Array and Non-Array bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1228 Named bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1268 - valueForUndefined should be in toSQL(QueryCompiler)'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1982 (2) - should throw error on non string'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1982 (3) - should throw error when there is subcomments'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1982 (4) - should throw error when there is question mark'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - allows hint comments in subqueries'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - forbids "/*", "*/" and "?" in hint comments'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#4199 - forbids non-strings as hint comments'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Any undefined binding in a SELECT query should throw an error'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Any undefined binding in a RAW query should throw an error'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Support escaping of named bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Respect casting with named bindings'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['query \\\\? escaping'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['operator transformation'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should return dialect specific sql and bindings with  toSQL().toNative()'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Oracle: withRecursive with column list'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with update query passed as raw'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
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
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with delete query passed as callback'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['with places bindings in correct order'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#1710, properly escapes arrays in where clauses in postgresql'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['#2003, properly escapes objects with toPostgres specialization'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Throws error if .update() results in faulty sql due to no data'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Throws error if .first() is called on update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Throws error if .first() is called on insert'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Throws error if .first() is called on delete'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can be used as parameter in where-clauses'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can use .as() for alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['Can call knex.select(0)'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should warn to user when use `.returning()` function in MySQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['join with subquery using .withSchema'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should join on a subquery with custom conditions and schema'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should join on a subquery defined as a function with custom conditions and schema'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not prepend schema to a subquery'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not prepend schema to a subquery specified by a function'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not prepend schema to a raw FROM clause'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should not prepend schema to a raw JOIN clause'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['join with onVal andOnVal orOnVal'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should transform joins into "using" syntax with PostgreSQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should transform multiple joins into multiple "using" syntax with PostgreSQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should join with "using" explicit syntax with PostgreSQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should join with multiple tables and "using" explicit syntax with PostgreSQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should joins with mixed joins and "using" explicit syntax with PostgreSQL'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should include join when deleting'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should include joins without where clause when deleting #5015'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should include joins clauses with multiple joins with where clause when deleting #5015'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should include join when deleting with mssql triggers'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should extract json value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should extract json value with alias'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should extract json values with mutiple extracts'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should set json value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should set json value with pg path syntax'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should set json value with nested function'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should insert json'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should remove path in json'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['should insert then extract'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where equals json'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where json column is equals to the value returned by a json path'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where a json column is a superset of value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where a json column is a superset of value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where a json column is not a superset of value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where a json column be a subset of value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['where a json column is not subset of value'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('my_table'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `my_schema`.`my_table`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };
        /*  Query Builder end  */

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
