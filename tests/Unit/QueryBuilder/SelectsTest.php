<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class SelectsTest extends \Codeception\Test\Unit
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

        // if(count($bindings) === 1 && is_array($bindings[0])) $bindings = $bindings[0];

        // $iRaw = new Raw($iClient);
        // $iRaw->set($query, $bindings);

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
                    ->select('foo')
                    ->select('bar')
                    ->select(['baz', 'boom'])
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo`, `bar`, `baz`, `boom` FROM `users`',
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
                    ->distinct()
                    ->select('foo', 'bar')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT DISTINCT `foo`, `bar` FROM `users`',
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
                    ->select(['bar'=>'foo'])
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo` AS `bar` FROM `users`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with mixed pure column and alias pair 2'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('baz', ['bar'=>'foo'])
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `baz`, `foo` AS `bar` FROM `users`',
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
                    ->select(['baz', ['bar'=>'foo']])
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `baz`, `foo` AS `bar` FROM `users`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic select with mixed pure column and alias pair 1'] = function()
        {
            $case =
            [
                self::qb()
                    ->select(['bar'=>'foo'])
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo` AS `bar` FROM `users`',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['basic old-style alias'] = function()
        {// 2023-05-16 Should propably deprecate this - Magnus
            $case =
            [
                self::qb()
                    ->select('foo as bar')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo` AS `bar` FROM `users`',
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
                    ->select(' foo   as bar ')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo` AS `bar` FROM `users`',
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
                    ->select(' foo   aS bar ')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `foo` AS `bar` FROM `users`',
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
                    ->select('foo as bar.baz')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        // 'sql'=>'SELECT `foo` AS `bar.baz` FROM `users`',
                        'sql'=>'SELECT `foo` AS `bar`.`baz` FROM `users`',
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
                    ->select([
                        'bar'=>'table1.*',
                        'subq'=>self::qb()
                            ->from('test')
                            ->select(self::raw('??', [['a'=>'col1', 'b'=>'col2']]))
                            ->limit(1)
                    ])
                    ->from([
                        'table1'=>'table',
                        'table2'=>'table',
                        'subq'=>self::qb()->from('test')->limit(1)
                    ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `table1`.* AS `bar`, (SELECT `col1` AS `a`, `col2` AS `b` FROM `test` LIMIT ?) AS `subq` FROM `table` AS `table1`, `table` AS `table2`, (SELECT * FROM `test` LIMIT ?) AS `subq`',
                        'bindings'=>[1, 1]
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
                    ->from('public.users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `public`.`users`',
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
                    ->withSchema('myschema')
                    ->select('*')
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `myschema`.`users`',
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
                    ->select(self::raw('substr(foo, 6)'))
                    ->from('users'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT substr(foo, 6) FROM `users`',
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
                    ->from('users')
                    ->count(),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(*) FROM `users`',
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
                    ->from('users')
                    ->countDistinct(),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT *) FROM `users`',
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
                    ->from('users')
                    ->count('* as all'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(*) AS `all` FROM `users`',
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
                    ->from('users')
                    ->count([ 'all'=>'*' ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(*) AS `all` FROM `users`',
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
                    ->from('users')
                    ->countDistinct('* as all'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT *) AS `all` FROM `users`',
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
                    ->from('users')
                    ->countDistinct([ 'all'=>'*' ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT *) AS `all` FROM `users`',
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
                    ->from('users')
                    ->count(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(`name`) FROM `users`',
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
                    ->from('users')
                    ->countDistinct(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT `name`) FROM `users`',
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
                    ->from('users')
                    ->countDistinct('foo', 'bar'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT `foo`, `bar`) FROM `users`',
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
                    ->from('users')
                    ->countDistinct([ 'alias'=>['foo', 'bar'] ]),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT COUNT(DISTINCT `foo`, `bar`) AS `alias` FROM `users`',
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
                    ->from('users')
                    ->max('id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT MAX(`id`) FROM `users`',
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
                    ->from('users')
                    ->max(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT MAX(`name`) FROM `users`',
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
                    ->from('users')
                    ->min('id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT MIN(`id`) FROM `users`',
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
                    ->from('users')
                    ->min(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT MIN(`name`) FROM `users`',
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
                    ->from('users')
                    ->sum('id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT SUM(`id`) FROM `users`',
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
                    ->from('users')
                    ->sum(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT SUM(`name`) FROM `users`',
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
                    ->from('users')
                    ->sumDistinct('id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT SUM(DISTINCT `id`) FROM `users`',
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
                    ->from('users')
                    ->sumDistinct(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT SUM(DISTINCT `name`) FROM `users`',
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
                    ->from('users')
                    ->avg('id'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT AVG(`id`) FROM `users`',
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
                    ->from('users')
                    ->avg(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT AVG(`name`) FROM `users`',
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
                    ->from('users')
                    ->avgDistinct(self::raw('??', 'name')),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT AVG(DISTINCT `name`) FROM `users`',
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

        $cases['#287 - wraps correctly for arrays'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('value')
                    ->join('table', 'table.array_column[1]', '=', self::raw('?', 1)),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `value` INNER JOIN `table` ON(`table`.`array_column[1]` = ?)',
                        'bindings'=>[1]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['allows wrap on raw to wrap in parens and alias'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->select('e.lastname', 'e.salary', self::raw(self::qb()->select('avg(salary)')->from('employee')->whereRaw('dept_no = e.dept_no'))->wrap('(', ') avg_sal_dept'))
        //             ->from('employee as e')
        //             ->where('dept_no', '=', 'e.dept_no'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `avg(salary)` FROM `employee` WHERE dept_no = e.dept_no) avg_sal_dept FROM `employee` AS `e` WHERE `dept_no` = ?',
        //                 'bindings'=>['e.dept_no']
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['allows select as syntax'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('e.lastname', 'e.salary', self::qb()->select('avg(salary)')->from('employee')->whereRaw('dept_no = e.dept_no')->as('avg_sal_dept'))
                    ->from('employee as e')
                    ->where('dept_no', '=', 'e.dept_no'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `avg(salary)` FROM `employee` WHERE dept_no = e.dept_no) AS `avg_sal_dept` FROM `employee` AS `e` WHERE `dept_no` = ?',
                        'bindings'=>['e.dept_no']
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
                    ->select('e.lastname', 'e.salary')
                    ->select(function($q) {
                        $q->select('avg(salary)')
                            ->from('employee')
                            ->whereRaw('dept_no = e.dept_no')
                            ->as('avg_sal_dept');
                    })
                    // ->as('avg_sal_dept')
                    ->from('employee as e')
                    ->where('dept_no', '=', 'e.dept_no'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `avg(salary)` FROM `employee` WHERE dept_no = e.dept_no) AS `avg_sal_dept` FROM `employee` AS `e` WHERE `dept_no` = ?',
                        'bindings'=>['e.dept_no']
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
                    ->select(
                        'e.lastname',
                        'e.salary',
                        self::qb()
                            ->first('salary')
                            ->from('employee')
                            ->whereRaw('dept_no = e.dept_no')
                            ->orderBy('salary', 'desc')
                            ->as('top_dept_salary')
                    )
                    ->from('employee as e')
                    ->where('dept_no', '=', 'e.dept_no'),

                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `salary` FROM `employee` WHERE dept_no = e.dept_no ORDER BY `salary` DESC LIMIT ?) AS `top_dept_salary` FROM `employee` AS `e` WHERE `dept_no` = ?',
                        'bindings'=>[1, 'e.dept_no']
                    ]
                ]
            ];

            return $case;
        };

        $cases['should always wrap subquery with parenthesis'] = function()
        {
            $subquery = self::qb()->select(self::raw('?', 'inner raw select'), 'bar');
            $case =
            [
                self::qb()
                    ->select(self::raw('?', 'outer raw select'))
                    ->from($subquery),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT ? FROM (SELECT ?, `bar`)',
                        'bindings'=>['outer raw select', 'inner raw select']
                    ]
                ]
            ];

            return $case;
        };

        $cases['correctly orders parameters when selecting from subqueries, #704'] = function()
        {
            $subquery = self::qb()
                ->select(['f'=>self::raw('?', 'inner raw select')])
                ->as('g');

            $case =
            [
                self::qb()
                    ->select(self::raw('?', 'outer raw select'), 'g.f')
                    ->from($subquery)
                    // ->as('g')
                    ->where('g.secret', 123),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT ?, `g`.`f` FROM (SELECT ? AS `f`) AS `g` WHERE `g`.`secret` = ?',
                        'bindings'=>['outer raw select', 'inner raw select', 123]
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
                    ->select('id","name', 'id`name')
                    ->from('test`'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT `id","name`, `id\`name` FROM `test\``',
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
                    ->fromRaw('(select * from users where age > 18)'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM (select * from users where age > 18)',
                        'bindings'=>[]
                    ]
                ]
            ];

            return $case;
        };

        $cases['raw'] = function()
        {
            $case =
            [
                self::qb()->raw('select * from users where age > 18'),
                [
                    'mysql'=>
                    [
                        'sql'=>'select * from users where age > 18',
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

        $iQuery = $iSharQCompiler->toQuery('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
