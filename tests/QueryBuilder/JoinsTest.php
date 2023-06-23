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

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['cross join'] = function()
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

            return $case;
        };

        // $cases['full outer join'] = function()
        // {
        //     $case =
        //     [
        //         self::qb()
        //             ->select('*')
        //         ->from('users')
        //         ->fullOuterJoin('contacts', 'user.id', '=', 'contacts.id'),
        //         [
        //             'mysql'=>
        //             [
        //                 'sql'=>'',
        //                 'bindings'=>[]
        //             ]
        //         ]
        //     ];
        //
        //     return $case;
        // };

        $cases['cross join on'] = function()
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

            return $case;
        };

        $cases['basic joins'] = function()
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

            return $case;
        };

        $cases['right (outer) joins'] = function()
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

            return $case;
        };

        $cases['complex join'] = function()
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

            return $case;
        };

        $cases['complex join with nest conditional statements'] = function()
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

            return $case;
        };

        $cases['complex join with empty in'] = function()
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

            return $case;
        };

        $cases['joins with raw'] = function()
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

            return $case;
        };

        $cases['joins with schema'] = function()
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
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `myschema`.`users` INNER JOIN `myschema`.`contacts` ON(`user`.`id` = `contacts`.`id`) LEFT JOIN `myschema`.`photos` ON(`users`.`id` = `photos`.`id`)',
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

            return $case;
        };

        $cases['or on null'] = function()
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

            return $case;
        };

        $cases['on not null'] = function()
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

            return $case;
        };

        $cases['or on not null'] = function()
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

            return $case;
        };

        $cases['on exists'] = function()
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

            return $case;
        };

        $cases['or on exists'] = function()
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

            return $case;
        };

        $cases['on not exists'] = function()
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

            return $case;
        };

        $cases['or on not exists'] = function()
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

            return $case;
        };

        $cases['on between'] = function()
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

            return $case;
        };

        $cases['or on between'] = function()
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

            return $case;
        };

        $cases['on not between'] = function()
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

            return $case;
        };

        $cases['or on not between'] = function()
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

            return $case;
        };

        $cases['on in'] = function()
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

            return $case;
        };

        $cases['or on in'] = function()
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

            return $case;
        };

        $cases['or on in with raw'] = function()
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

            return $case;
        };

        $cases['on not in'] = function()
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

            return $case;
        };

        $cases['or on not in'] = function()
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

            return $case;
        };

        $cases['allows left outer join with raw values'] = function()
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

            return $case;
        };

        // $cases['on json path join'] = function()
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

        $cases['has joinRaw for arbitrary join clauses'] = function()
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

            return $case;
        };

        $cases['allows a raw query in the second param'] = function()
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

            return $case;
        };

        $cases['allows join "using" 1'] = function()
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

            return $case;
        };

        $cases['allows join "using" 2'] = function()
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

            return $case;
        };

        $cases['allows for raw values in join, #441'] = function()
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

            return $case;
        };

        $cases['allows join without operator and with value 0 #953'] = function()
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

            return $case;
        };

        $cases['allows join with operator and with value 0 #953'] = function()
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

            return $case;
        };

        $cases['join with alias'] = function()
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

            return $case;
        };

        $cases['Join on query'] = function()
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

        $iQuery = $iQueryCompiler->toSQL('select');
        $sqlAndBindings =
        [
            'sql'=>$iQuery->getSQL(),
            'bindings'=>$iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}


