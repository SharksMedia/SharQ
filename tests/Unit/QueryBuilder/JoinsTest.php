<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class JoinsTest extends \Codeception\Test\Unit
{
    public static function getClient(): MySQL
    {// 2023-05-16
        $iConfig = new Config('mysql');
        $iClient = new MySQL($iConfig);

        return $iClient;
    }

    /**
     * @param string $query
     * @param mixed $bindings
     * @return Raw
     */
    public static function raw(string $query, ...$bindings): Raw
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

    /**
     * @param SharQ $iSharQ
     * @param array<string, array> $iExpected
     * @return void
     */
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

    public function testCrossJoin(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->crossJoin('contracts')
                ->crossJoin('photos'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` CROSS JOIN `contracts` CROSS JOIN `photos`',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testCrossJoinOn(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->crossJoin('contracts', 'users.contractId', 'contracts.id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` CROSS JOIN `contracts` ON(`users`.`contractId` = `contracts`.`id`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testBasicJoins(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('contacts', 'users.id', '=', 'contacts.id')
                ->leftJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) LEFT JOIN `photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testRightOuterJoins(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->rightJoin('contacts', 'users.id', '=', 'contacts.id')
                ->rightOuterJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` RIGHT JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) RIGHT OUTER JOIN `photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testComplexJoin(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` OR `users`.`name` = `contacts`.`name`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testComplexJoinWithNestConditionalStatements(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON((`users`.`id` = `contacts`.`id` OR `users`.`name` = `contacts`.`name`))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testComplexJoinWithEmptyIn(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND 1 = 0)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function joinsWithRaw(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`user`.`id` = 1) LEFT JOIN `photos` ON(`photos`.`title` = ?)',
                    'bindings' => ['My Photo']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function joinsWithSchema(): void
    {
        $case =
        [
            self::qb()
                ->withSchema('myschema')
                ->select('*')
                ->from('users')
                ->join('contacts', 'user.id', '=', 'contacts.id')
                ->leftJoin('photos', 'users.id', '=', 'photos.id'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `myschema`.`users` INNER JOIN `myschema`.`contacts` ON(`user`.`id` = `contacts`.`id`) LEFT JOIN `myschema`.`photos` ON(`users`.`id` = `photos`.`id`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnNull(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NULL)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnNull(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NULL OR `contacts`.`phone` IS NULL)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnNotNull(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NOT NULL)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnNotNull(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`address` IS NOT NULL OR `contacts`.`phone` IS NOT NULL)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnExists(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND EXISTS(SELECT * FROM `foo`))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnExists(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND EXISTS(SELECT * FROM `foo`) OR EXISTS(SELECT * FROM `bar`))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnNotExists(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND NOT EXISTS(SELECT * FROM `foo`))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnNotExists(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND NOT EXISTS(SELECT * FROM `foo`) OR NOT EXISTS(SELECT * FROM `bar`))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnBetween(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` BETWEEN ? AND ?)',
                    'bindings' => [7, 15]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnBetween(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` BETWEEN ? AND ? OR `users`.`id` BETWEEN ? AND ?)',
                    'bindings' => [7, 15, 9, 14]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnNotBetween(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT BETWEEN ? AND ?)',
                    'bindings' => [7, 15]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnNotBetween(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT BETWEEN ? AND ? OR `users`.`id` NOT BETWEEN ? AND ?)',
                    'bindings' => [7, 15, 9, 14]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnIn(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?))',
                    'bindings' => [7, 15, 23, 41]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnIn(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?) OR `users`.`id` IN(?, ?))',
                    'bindings' => [7, 15, 23, 41, 21, 37]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnInWithRaw(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` IN(?, ?, ?, ?) OR `users`.`id` IN(SELECT `id` FROM `users` WHERE `age` > ?))',
                    'bindings' => [7, 15, 23, 41, 18]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOnNotIn(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT IN(?, ?, ?, ?))',
                    'bindings' => [7, 15, 23, 41]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testOrOnNotIn(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id` AND `contacts`.`id` NOT IN(?, ?, ?, ?) OR `users`.`id` NOT IN(?, ?))',
                    'bindings' => [7, 15, 23, 41, 21, 37]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsLeftOuterJoinWithRawValues(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `student` LEFT OUTER JOIN `student_languages` ON(`student`.`id` = `student_languages`.`student_id` AND `student_languages`.`code` = ?)',
                    'bindings' => ['en_US']
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testHasJoinrawForArbitraryJoinClauses(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->joinRaw('natural full join table1')
                ->where('id', 1),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `accounts` natural full join table1 WHERE `id` = ?',
                    'bindings' => [1]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsARawQueryInTheSecondParam(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('accounts')
                ->innerJoin('table1', self::raw('ST_Contains(buildings_pluto.geom, ST_Centroid(buildings_building.geom))')),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `accounts` INNER JOIN `table1` ON(ST_Contains(buildings_pluto.geom, ST_Centroid(buildings_building.geom)))',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsJoinUsing1(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `accounts` INNER JOIN `table1` USING(`id`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsJoinUsing2(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `accounts` INNER JOIN `table1` USING(`id`, `test`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function allowsForRawValuesInJoin441(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT `A`.`nid` AS `id` FROM nidmap2 AS A INNER JOIN (SELECT MIN(nid) AS location_id FROM nidmap2) AS B ON(`A`.`x` = `B`.`x`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function allowsJoinWithoutOperatorAndWithValue0953(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos', 'photos.id', 0),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `photos` ON(`photos`.`id` = ?)',
                    'bindings' => [0]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testAllowsJoinWithOperatorAndWithValue0953(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos', 'photos.id', '>', 0),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `photos` ON(`photos`.`id` > ?)',
                    'bindings' => [0]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testJoinWithAlias(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->join('photos as p', 'p.id', '>', 0),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `photos` AS `p` ON(`p`.`id` > ?)',
                    'bindings' => [0]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testJoinOnQuery(): void
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
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN (SELECT `userID` FROM `users` WHERE `userID` = ?) AS `u` ON(`users`.`userID` = `p`.`userID`)',
                    'bindings' => [1]
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }

    public function testLeftJoinFollowedByNormalJoin(): void
    {
        $case =
        [
            self::qb()
                ->select('*')
                ->from('users')
                ->leftJoin('a', 'a.id', '=', 'p.userID')
                ->join('p', 'users.userID', '=', 'p.userID'),
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` LEFT JOIN `a` ON(`a`.`id` = `p`.`userID`) INNER JOIN `p` ON(`users`.`userID` = `p`.`userID`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }
}
