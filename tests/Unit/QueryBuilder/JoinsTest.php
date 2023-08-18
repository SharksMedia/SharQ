<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\QueryBuilder\QueryBuilder;
use Sharksmedia\QueryBuilder\Client\MySQL;
use Sharksmedia\QueryBuilder\Config;

use Sharksmedia\QueryBuilder\QueryCompiler;
use Sharksmedia\QueryBuilder\Statement\Raw;

class JoinsTest extends \Codeception\Test\Unit
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

    public function _testQueryBuilder(QueryBuilder $iQueryBuilder, array $iExpected)
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

    public function testCrossJoin()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->crossJoin('contracts')
                ->crossJoin('photos'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` CROSS JOIN `contracts` CROSS JOIN `photos`',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testCrossJoinOn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->crossJoin('contracts', 'users.contractId', 'contracts.id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` CROSS JOIN `contracts` ON(`users`.`contractId` = `contracts`.`id`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testBasicJoins()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', 'users.id', '=', 'contacts.id')
                ->leftJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) LEFT JOIN `photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testRightOuterJoins()
    {
        $case =
        [
            self::qb()
                ->select('*')
            ->from('users')
            ->rightJoin('contacts', 'users.id', '=', 'contacts.id')
            ->rightOuterJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` RIGHT JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) RIGHT OUTER JOIN `photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testComplexJoin()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->orOn('users.name', '=', 'contacts.name');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` OR `users`.`name` = `contacts`.`name`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testComplexJoinWithNestConditionalStatements()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on(function($q)
                    {
                        $q->on('users.id', '=', 'contacts.id')
                          ->orOn('users.name', '=', 'contacts.name');
                    });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON((`users`.`id` = `contacts`.`id` OR `users`.`name` = `contacts`.`name`))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testComplexJoinWithEmptyIn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onIn('users.name', []);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND 1 = 0)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function joinsWithRaw()
    {
        $case =
        [
            self::qb()
                ->select('*')
            ->from('users')
            ->join('contacts', 'user.id', self::raw(1))
            // ->leftJoin('photos', 'photos.title', '=', self::raw('?', ['My Photo'])),
            ->leftJoin('photos', 'photos.title', '=', self::raw('?', 'My Photo')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`user`.`id` = 1) LEFT JOIN `photos` ON(`photos`.`title` = ?)',
                    'bindings'=>['My Photo']
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function joinsWithSchema()
    {
        $testCase =
        [
            self::qb()
            ->withSchema('myschema')
            ->select('*')
            ->from('users')
            ->join('contacts', 'user.id', '=', 'contacts.id')
            ->leftJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `myschema`.`users` INNER JOIN `myschema`.`contacts` ON(`user`.`id` = `contacts`.`id`) LEFT JOIN `myschema`.`photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnNull()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNull('contacts.address');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NULL)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnNull()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                    ->onNull('contacts.address')
                    ->orOnNull('contacts.phone');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NULL OR `contacts`.`phone` IS NULL)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnNotNull()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                    ->onNotNull('contacts.address');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NOT NULL)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnNotNull()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotNull('contacts.address')
                      ->orOnNotNull('contacts.phone');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NOT NULL OR `contacts`.`phone` IS NOT NULL)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnExists()
    {
        $case =
        [
            self::qb()
                ->select('*')
            ->from('users')
            ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onExists(function($q)
                      {
                          $q->select('*')
                            ->from('foo');
                      });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND EXISTS(SELECT * FROM `foo`))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnExists()
    {
        $case =
        [
            self::qb()
                ->select('*')
            ->from('users')
            ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onExists(function($q)
                        {
                            $q->select('*')
                              ->from('foo');
                        })
                        ->orOnExists(function($q)
                        {
                            $q->select('*')
                              ->from('bar');
                        });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND EXISTS(SELECT * FROM `foo`) OR EXISTS(SELECT * FROM `bar`))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnNotExists()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotExists(function($q)
                        {
                            $q->select('*')
                              ->from('foo');
                        });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND NOT EXISTS(SELECT * FROM `foo`))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnNotExists()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotExists(function($q)
                        {
                            $q->select('*')
                              ->from('foo');
                        })
                        ->orOnNotExists(function($q)
                        {
                            $q->select('*')
                              ->from('bar');
                        });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND NOT EXISTS(SELECT * FROM `foo`) OR NOT EXISTS(SELECT * FROM `bar`))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnBetween()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onBetween('contacts.id', [7, 15]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` BETWEEN ? AND ?)',
                    'bindings'=>[7, 15]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnBetween()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onBetween('contacts.id', [7, 15])
                      ->orOnBetween('users.id', [9, 14]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` BETWEEN ? AND ? OR `users`.`id` BETWEEN ? AND ?)',
                    'bindings'=>[7, 15, 9, 14]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnNotBetween()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotBetween('contacts.id', [7, 15]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT BETWEEN ? AND ?)',
                    'bindings'=>[7, 15]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnNotBetween()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotBetween('contacts.id', [7, 15])
                      ->orOnNotBetween('users.id', [9, 14]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT BETWEEN ? AND ? OR `users`.`id` NOT BETWEEN ? AND ?)',
                    'bindings'=>[7, 15, 9, 14]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnIn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onIn('contacts.id', [7, 15, 23, 41]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?))',
                    'bindings'=>[7, 15, 23, 41]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnIn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onIn('contacts.id', [7, 15, 23, 41])
                      ->orOnIn('users.id', [21, 37]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?) OR `users`.`id` IN(?, ?))',
                    'bindings'=>[7, 15, 23, 41, 21, 37]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnInWithRaw()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onIn('contacts.id', [7, 15, 23, 41])
                      ->orOnIn('users.id', function($q)
                      {
                          $q->select('id')
                            ->from('users')
                            ->where('age', '>', 18);
                      });
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?) OR `users`.`id` IN(SELECT `id` FROM `users` WHERE `age` > ?))',
                    'bindings'=>[7, 15, 23, 41, 18]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOnNotIn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotIn('contacts.id', [7, 15, 23, 41]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT IN(?, ?, ?, ?))',
                    'bindings'=>[7, 15, 23, 41]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testOrOnNotIn()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', function($q)
                {
                    $q->on('users.id', '=', 'contacts.id')
                      ->onNotIn('contacts.id', [7, 15, 23, 41])
                      ->orOnNotIn('users.id', [21, 37]);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT IN(?, ?, ?, ?) OR `users`.`id` NOT IN(?, ?))',
                    'bindings'=>[7, 15, 23, 41, 21, 37]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testAllowsLeftOuterJoinWithRawValues()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('student')
                ->leftOuterJoin('student_languages', function($q)
                {
                    $q->on('student.id', 'student_languages.student_id')
                      ->andOn('student_languages.code', self::raw('?', 'en_US'));
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `student` LEFT OUTER JOIN `student_languages` ON(`student`.`id` = `student_languages`.`student_id` AND `student_languages`.`code` = ?)',
                    'bindings'=>['en_US']
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testHasJoinrawForArbitraryJoinClauses()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->joinRaw('natural full join table1')
                ->where('id', 1),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `accounts` natural full join table1 WHERE `id` = ?',
                    'bindings'=>[1]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testAllowsARawQueryInTheSecondParam()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->innerJoin('table1', self::raw('ST_Contains(buildings_pluto.geom, ST_Centroid(buildings_building.geom))')),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `accounts` INNER JOIN `table1` ON(ST_Contains(buildings_pluto.geom, ST_Centroid(buildings_building.geom)))',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testAllowsJoinUsing1()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->innerJoin('table1', function($q)
                {
                    $q->using('id');
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `accounts` INNER JOIN `table1` USING(`id`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testAllowsJoinUsing2()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->innerJoin('table1', function($q)
                {
                    $q->using(['id', 'test']);
                }),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `accounts` INNER JOIN `table1` USING(`id`, `test`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function allowsForRawValuesInJoin441()
    {
        $case =
        [
            self::qb()
                ->select('A.nid AS id')
                ->from(self::raw('nidmap2 AS A'))
                ->innerJoin(
                    self::raw('SELECT MIN(nid) AS location_id FROM nidmap2')->wrap('(', ') AS B'),
                    'A.x',
                    '=',
                    'B.x'
                ),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT `A`.`nid` AS `id` FROM nidmap2 AS A INNER JOIN (SELECT MIN(nid) AS location_id FROM nidmap2) AS B ON(`A`.`x` = `B`.`x`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function allowsJoinWithoutOperatorAndWithValue0953()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos', 'photos.id', 0),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `photos` ON(`photos`.`id` = ?)',
                    'bindings'=>[0]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testAllowsJoinWithOperatorAndWithValue0953()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos', 'photos.id', '>', 0),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `photos` ON(`photos`.`id` > ?)',
                    'bindings'=>[0]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testJoinWithAlias()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos as p', 'p.id', '>', 0),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN `photos` AS `p` ON(`p`.`id` > ?)',
                    'bindings'=>[0]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testJoinOnQuery()
    {
        $qb = self::qb()
                ->select('userID')
                ->from('users')
                ->where('userID', 1)
                ->as('u');

        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join($qb, 'users.userID', '=', 'p.userID'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` INNER JOIN (SELECT `userID` FROM `users` WHERE `userID` = ?) AS `u` ON(`users`.`userID` = `p`.`userID`)',
                    'bindings'=>[1]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    public function testLeftJoinFollowedByNormalJoin()
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->leftJoin('a', 'a.id', '=', 'p.userID')
                ->join('p', 'users.userID', '=', 'p.userID'),
            [
                'mysql'=>
                [
                    'sql'=>'SELECT * FROM `users` LEFT JOIN `a` ON(`a`.`id` = `p`.`userID`) INNER JOIN `p` ON(`users`.`userID` = `p`.`userID`)',
                    'bindings'=>[]
                ]
            ]
        ];

        return $this->_testQueryBuilder(...$case);
    }

    
}
