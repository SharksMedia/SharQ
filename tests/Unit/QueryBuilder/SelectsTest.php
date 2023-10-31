<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Config;
use Sharksmedia\SharQ\Query;
use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;
use Tests\MockMySQLClient;
use Tests\MockPDOStatement;

class SelectsTest extends \Codeception\Test\Unit
{
    public static function getClient(array $results = [])
    {// 2023-05-16
        $iConfig = new Config('mysql');
        // $iClient = new MySQL($iConfig);

        $iClient = new MockMySQLClient($iConfig, function(Query $iQuery) use ($results)
        {
            // $sql      = $iQuery->getSQL();
            // $bindings = $iQuery->getBindings();

            $iPDOStatement = new MockPDOStatement();

            $iPDOStatement->setResults($results ?? []);

            // $this->executedQueries[] =
            // [
            //     'sql'      => $sql,
            //     'bindings' => $bindings
            // ];

            return $iPDOStatement;
        });

        $iClient->initializeDriver();

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

    private static function qb(array $results = []): SharQ
    {// 2023-05-16
        $iClient = self::getClient($results);

        return new SharQ($iClient, 'my_schema');
    }

    public function testBasicSelect()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAddingSelects()
    {
        $case =
        [
            self::qb()
                ->select('foo')
                ->select('bar')
                ->select(['baz', 'boom'])
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo`, `bar`, `baz`, `boom` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectDistinct()
    {
        $case =
        [
            self::qb()
                ->distinct()
                ->select('foo', 'bar')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT DISTINCT `foo`, `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectDistinct2()
    {
        $case =
        [
            self::qb()
                ->distinct('foo', 'bar')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT DISTINCT `foo`, `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectWithAliasAsPropertyValuePairs()
    {
        $case =
        [
            self::qb()
                ->select(['bar' => 'foo'])
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectWithMixedPureColumnAndAliasPair2()
    {
        $case =
        [
            self::qb()
                ->select('baz', ['bar' => 'foo'])
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `baz`, `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectWithArrayWrappedAliasPair()
    {
        $case =
        [
            self::qb()
                ->select(['baz', ['bar' => 'foo']])
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `baz`, `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicSelectWithMixedPureColumnAndAliasPair1()
    {
        $case =
        [
            self::qb()
                ->select(['bar' => 'foo'])
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicOldStyleAlias()
    {
        $case =
        [
            self::qb()
                ->select('foo as bar')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicAliasTrimsSpaces()
    {
        $case =
        [
            self::qb()
                ->select(' foo   as bar ')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsForCaseInsensitiveAlias()
    {
        $case =
        [
            self::qb()
                ->select(' foo   aS bar ')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `foo` AS `bar` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsAliasWithDotsInTheIdentifierName()
    {
        $case =
        [
            self::qb()
                ->select('foo as bar.baz')
                ->from('users'),
            [
                'mysql' =>
                [
                    // 'sql'=>'SELECT `foo` AS `bar.baz` FROM `users`',
                    'sql'      => 'SELECT `foo` AS `bar`.`baz` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testLessTrivialCaseOfObjectAliasSyntax()
    {
        $case =
        [
            self::qb()
                ->select([
                    'bar'  => 'table1.*',
                    'subq' => self::qb()
                        ->from('test')
                        ->select(self::raw('??', [['a' => 'col1', 'b' => 'col2']]))
                        ->limit(1)
                ])
                ->from([
                    'table1' => 'table',
                    'table2' => 'table',
                    'subq'   => self::qb()->from('test')->limit(1)
                ]),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `table1`.* AS `bar`, (SELECT `col1` AS `a`, `col2` AS `b` FROM `test` LIMIT ?) AS `subq` FROM `table` AS `table1`, `table` AS `table2`, (SELECT * FROM `test` LIMIT ?) AS `subq`',
                    'bindings' => [1, 1]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicTableWrapping()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('public.users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `public`.`users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicTableWrappingWithDeclaredSchema()
    {
        $case =
        [
            self::qb()
                ->withSchema('myschema')
                ->select('*')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `myschema`.`users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testRawExpressionsInSelect()
    {
        $case =
        [
            self::qb()
                ->select(self::raw('substr(foo, 6)'))
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT substr(foo, 6) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCount()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->count(),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(*) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinct()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct(),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT *) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountWithStringAlias()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->count('* as all'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(*) AS `all` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountWithObjectAlias()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->count([ 'all' => '*' ]),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(*) AS `all` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinctWithStringAlias()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct('* as all'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT *) AS `all` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinctWithObjectAlias()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct([ 'all' => '*' ]),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT *) AS `all` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->count(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(`name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinctWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT `name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinctWithMultipleColumns()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct('foo', 'bar'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT `foo`, `bar`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCountDistinctWithMultipleColumnsWithAlias()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->countDistinct([ 'alias' => ['foo', 'bar'] ]),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT COUNT(DISTINCT `foo`, `bar`) AS `alias` FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testMax()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->max('id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT MAX(`id`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testMaxWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->max(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT MAX(`name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testMin()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->min('id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT MIN(`id`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testMinWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->min(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT MIN(`name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testSum()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->sum('id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT SUM(`id`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testSumWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->sum(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT SUM(`name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testSumDistinct()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->sumDistinct('id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT SUM(DISTINCT `id`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testSumDistinctWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->sumDistinct(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT SUM(DISTINCT `name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAvg()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->avg('id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT AVG(`id`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAvgWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->avg(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT AVG(`name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAvgDistinctWithRawValues()
    {
        $case =
        [
            self::qb()
                ->from('users')
                ->avgDistinct(self::raw('??', 'name')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT AVG(DISTINCT `name`) FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testWrapping()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function test287WrapsCorrectlyForArrays()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('value')
                ->join('table', 'table.array_column[1]', '=', self::raw('?', 1)),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `value` INNER JOIN `table` ON(`table`.`array_column[1]` = ?)',
                    'bindings' => [1]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsSelectAsSyntax()
    {
        $case =
        [
            self::qb()
                ->select('e.lastname', 'e.salary', self::qb()->select('avg(salary)')->from('employee')->whereRaw('dept_no = e.dept_no')->as('avg_sal_dept'))
                ->from('employee as e')
                ->where('dept_no', '=', 'e.dept_no'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `avg(salary)` FROM `employee` WHERE dept_no = e.dept_no) AS `avg_sal_dept` FROM `employee` AS `e` WHERE `dept_no` = ?',
                    'bindings' => ['e.dept_no']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsFunctionForSubselectColumn()
    {
        $case =
        [
            self::qb()
                ->select('e.lastname', 'e.salary')
                ->select(function($q)
                {
                    $q->select('avg(salary)')
                        ->from('employee')
                        ->whereRaw('dept_no = e.dept_no')
                        ->as('avg_sal_dept');
                })
                // ->as('avg_sal_dept')
                ->from('employee as e')
                ->where('dept_no', '=', 'e.dept_no'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `avg(salary)` FROM `employee` WHERE dept_no = e.dept_no) AS `avg_sal_dept` FROM `employee` AS `e` WHERE `dept_no` = ?',
                    'bindings' => ['e.dept_no']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsFirstAsSyntax()
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
                'mysql' =>
                [
                    'sql'      => 'SELECT `e`.`lastname`, `e`.`salary`, (SELECT `salary` FROM `employee` WHERE dept_no = e.dept_no ORDER BY `salary` DESC LIMIT ?) AS `top_dept_salary` FROM `employee` AS `e` WHERE `dept_no` = ?',
                    'bindings' => [1, 'e.dept_no']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testShouldAlwaysWrapSubqueryWithParenthesis()
    {
        $subquery = self::qb()->select(self::raw('?', 'inner raw select'), 'bar');

        $case =
        [
            self::qb()
                ->select(self::raw('?', 'outer raw select'))
                ->from($subquery),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT ? FROM (SELECT ?, `bar`)',
                    'bindings' => ['outer raw select', 'inner raw select']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCorrectlyOrdersParametersWhenSelectingFromSubqueries704()
    {
        $subquery = self::qb()
            ->select(['f' => self::raw('?', 'inner raw select')])
            ->as('g');

        $case =
        [
            self::qb()
                ->select(self::raw('?', 'outer raw select'), 'g.f')
                ->from($subquery)
                // ->as('g')
                ->where('g.secret', 123),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT ?, `g`.`f` FROM (SELECT ? AS `f`) AS `g` WHERE `g`.`secret` = ?',
                    'bindings' => ['outer raw select', 'inner raw select', 123]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testEscapesQueriesProperly737()
    {
        $case =
        [
            self::qb()
                ->select('id","name', 'id`name')
                ->from('test`'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `id","name`, `id\`name` FROM `test\``',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testUsesFromrawApi1767()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->fromRaw('(select * from users where age > 18)'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM (select * from users where age > 18)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testRaw()
    {
        $case =
        [
            self::qb()->raw('select * from users where age > 18'),
            [
                'mysql' =>
                [
                    'sql'      => 'select * from users where age > 18',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testRawAggregateWithAlias()
    {
        $case =
        [
            self::qb()
                ->select('productID')
                ->sum(self::raw('price * quantity'), ['as' => 'totalPrice'])
                ->from('products'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT `productID`, SUM(price * quantity) AS `totalPrice` FROM `products`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function _testSharQ(SharQ $iSharQ, array $iExpected): void
    {
        $iSharQCompiler = new SharQCompiler(self::getClient(), $iSharQ, []);

        $iQuery         = $iSharQCompiler->toQuery('select');
        $sqlAndBindings =
        [
            'sql'      => $iQuery->getSQL(),
            'bindings' => $iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }

    public function testFirstMethod()
    {
        // Insert a sample user into the database

        $results = [ [
            'id'         => 1,
            'name'       => 'John Doe',
            'email'      => 'john.doe@example.com',
            'password'   => '123456',
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
            'deleted_at' => null,
        ]];

        // Execute the query
        $qb = self::qb($results)->select('*')
            ->from('users')
            ->where('id', 1)
            ->first();

        $user = $qb->run();

        // The expected result
        $expected = [
            'id'         => 1,
            'name'       => 'John Doe',
            'email'      => 'john.doe@example.com',
            'password'   => '123456',
            'created_at' => '2020-01-01 00:00:00',
            'updated_at' => '2020-01-01 00:00:00',
            'deleted_at' => null,
        ];

        // Actual test assertion
        $this->assertEquals($expected, $user);
    }
}
