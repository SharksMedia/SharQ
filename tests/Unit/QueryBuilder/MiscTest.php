<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class MiscTest extends \Codeception\Test\Unit
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

    private static function qb(): SharQ
    {// 2023-05-16
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['full sub selects'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                            ->from('users')
                            ->where('email', '=', 'bar');
                    }),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `email` FROM `users` WHERE `email` = ? OR `id` = (SELECT MAX(id) FROM `users` WHERE `email` = ?)',
                        'bindings' => ['foo', 'bar']
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
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                            ->from('users')
                            ->where('email', '=', 'bar')
                            ->clearSelect();
                    }),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `email` FROM `users` WHERE `email` = ? OR `id` = (SELECT * FROM `users` WHERE `email` = ?)',
                        'bindings' => ['foo', 'bar']
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
                    ->select('email')
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->orWhere('id', '=', function($q)
                    {
                        $q->select(self::raw('MAX(id)'))
                            ->from('users')
                            ->where('email', '=', 'bar');
                    })
                    ->clearSelect(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `users` WHERE `email` = ? OR `id` = (SELECT MAX(id) FROM `users` WHERE `email` = ?)',
                        'bindings' => ['foo', 'bar']
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
                    ->from('places')
                    ->where(
                        self::raw(
                            'ST_DWithin((places.address).xy, ??, ?) AND ST_Distance((places.address).xy, ??) > ? AND ??',
                            self::raw('ST_SetSRID(??,?)', self::raw('ST_MakePoint(?,?)', -10, 10), 4326),
                            100000,
                            self::raw('ST_SetSRID(??,?)', self::raw('ST_MakePoint(?,?)', -5, 5), 4326),
                            50000,
                            self::raw('places.id IN (?, ?, ?)', 1, 2, 3),
                        )
                    ),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `places` WHERE ST_DWithin((places.address).xy, ST_SetSRID(ST_MakePoint(?,?),?), ?) AND ST_Distance((places.address).xy, ST_SetSRID(ST_MakePoint(?,?),?)) > ? AND places.id IN (?, ?, ?)',
                        'bindings' => [-10, 10, 4326, 100000, -5, 5, 4326, 50000, 1, 2, 3]
                    ]
                ]
            ];
            /*

            */

            return $case;
        };

        $cases['has a modify method which accepts a function that can modify the query'] = function()
        {
            // arbitrary number of arguments can be passed to `.modify(queryBuilder, ...)`,
            $withBars = function($queryBuilder, $table, $fk)
            {
                $queryBuilder->leftJoin('bars', $table.'.'.$fk, 'bars.id')->select('bars.*');
            };

            $case =
            [
                self::qb()
                    ->select('foo_id')
                    ->from('foos')
                    ->modify($withBars, 'foos', 'bar_id'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT `foo_id`, `bars`.* FROM `foos` LEFT JOIN `bars` ON(`foos`.`bar_id` = `bars`.`id`)',
                        'bindings' => []
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
                    ->from('testtable')
                    ->hintComment('hint()'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT /*+ hint() */ * FROM `testtable`',
                        'bindings' => []
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
                    ->from('testtable')
                    ->hintComment('hint1()', 'hint2()')
                    ->hintComment('hint3()'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT /*+ hint1() hint2() hint3() */ * FROM `testtable`',
                        'bindings' => []
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
                    ->from('testtable')
                    ->comment('Added comment 1')
                    ->comment('Added comment 2'),
                [
                    'mysql' =>
                    [
                        'sql'      => '/* Added comment 1 */ /* Added comment 2 */ SELECT * FROM `testtable`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        // $cases['#1982 (2) - should throw error on non string'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        // $cases['#1982 (3) - should throw error when there is subcomments'] = function()
        // {
        //      // FIXME: Implement me!
        // };

        // $cases['#1982 (4) - should throw error when there is question mark'] = function()
        // {
        //       // FIXME: Implement me!
        // };

        $cases['#4199 - allows hint comments in subqueries'] = function()
        {
            $case =
            [
                self::qb()
                    ->select([
                        'c1' => 'c1',
                        'c2' => self::qb()->select('c2')->from('t2')->hintComment('hint2()')->limit(1)
                    ])
                    ->from('t1')
                    ->hintComment('hint1()'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT /*+ hint1() */ `c1` AS `c1`, (SELECT /*+ hint2() */ `c2` FROM `t2` LIMIT ?) AS `c2` FROM `t1`',
                        'bindings' => [1]
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
                    ->from('t1')
                    ->hintComment('hint1()')
                    ->unionAll(self::qb()->from('t2')->hintComment('hint2()')),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT /*+ hint1() */ * FROM `t1` UNION ALL SELECT /*+ hint2() */ * FROM `t2`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        $cases['Med24 get content drug approvals'] = function()
        {
            $languageQuery = self::qb()->select('languages_id')->from('languages_to_stores')->whereColumn('stores_id', '=', 'cda.storeID');

            $q1 = self::qb()
                ->select(['cda.*', 'name' => self::raw('pd.products_name COLLATE utf8mb4_unicode_ci')])
                ->from(['cda' => 'ContentDrugApprovals'])
                ->join(self::raw('products_to_stores AS pts'), function($q)
                {
                    $q->on('cda.entityID', '=', 'pts.products_id')
                        ->andOn('cda.storeID', '=', 'pts.stores_id');
                })
                ->join(self::raw('products_description AS pd'), function($q) use ($languageQuery)
                {
                    $q->on('cda.entityID', '=', 'pd.products_id')
                        ->andOn('pd.language_id', '=', $languageQuery);
                })
                ->where('cda.entityType', '=', 'product');


            $q2 = self::qb()
                ->select(['cda.*', 'name' => 'al.name'])
                ->from(['cda' => 'ContentDrugApprovals'])
                ->join(self::raw('ArticleLocalizations AS al'), function($q) use ($languageQuery)
                {
                    $q->on('cda.entityID', '=', 'al.articleID')
                        ->andOn('al.languageID', '=', $languageQuery);
                })
                ->join(self::raw('Articles AS a'), 'a.articleID', '=', 'al.articleID')
                ->join(self::raw('ContentBlocks AS cb'), function($q)
                {
                    $q->on('cda.entityID', '=', 'cb.entityID')
                        ->andOn('cb.contentBlockTypeID', '=', self::qb()->select('contentBlockTypeID')->from('ContentBlockTypes')->where('name', '=', 'article'));
                })
                ->join(self::raw('ContentBlockSources AS cbs'), function($q)
                {
                    $q->on('cb.contentBlockID', '=', 'cbs.contentBlockID')
                        ->andOn('cbs.languageID', '=', 'al.languageID');
                })
                ->where('cda.entityType', '=', 'article')
                ->andWhere('cbs.sourceLongtext', '!=', '')
                ->andWhere('cbs.sourceLongtext', '!=', '[]')
                ->andWhere('al.enabled', '=', 1)
                ->andWhere('a.deleted', '=', 0);


            $query = self::qb()
                ->select('*')
                ->from(function($q) use ($q1, $q2)
                {
                    $q->union($q1->as('q1'), $q2->as('q2'))
                        ->as('T');
                })
                ->where('approved', '=', 1);

            $case =
            [
                $query,
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM (SELECT `cda`.*, pd.products_name COLLATE utf8mb4_unicode_ci AS `name` FROM `ContentDrugApprovals` AS `cda` INNER JOIN products_to_stores AS pts ON(`cda`.`entityID` = `pts`.`products_id` AND `cda`.`storeID` = `pts`.`stores_id`) INNER JOIN products_description AS pd ON(`cda`.`entityID` = `pd`.`products_id` AND `pd`.`language_id` = (SELECT `languages_id` FROM `languages_to_stores` WHERE `stores_id` = `cda`.`storeID`)) WHERE `cda`.`entityType` = ? UNION SELECT `cda`.*, `al`.`name` AS `name` FROM `ContentDrugApprovals` AS `cda` INNER JOIN ArticleLocalizations AS al ON(`cda`.`entityID` = `al`.`articleID` AND `al`.`languageID` = (SELECT `languages_id` FROM `languages_to_stores` WHERE `stores_id` = `cda`.`storeID`)) INNER JOIN Articles AS a ON(`a`.`articleID` = `al`.`articleID`) INNER JOIN ContentBlocks AS cb ON(`cda`.`entityID` = `cb`.`entityID` AND `cb`.`contentBlockTypeID` = (SELECT `contentBlockTypeID` FROM `ContentBlockTypes` WHERE `name` = ?)) INNER JOIN ContentBlockSources AS cbs ON(`cb`.`contentBlockID` = `cbs`.`contentBlockID` AND `cbs`.`languageID` = `al`.`languageID`) WHERE `cda`.`entityType` = ? AND `cbs`.`sourceLongtext` != ? AND `cbs`.`sourceLongtext` != ? AND `al`.`enabled` = ? AND `a`.`deleted` = ?) AS `T` WHERE `approved` = ?',
                        'bindings' => ['product', 'article', 'article', '', '[]', 1, 0, 1]
                    ]
                ]
            ];

            return $case;
        };


        $cases['Med24 get content drug approvals'] = function()
        {
            function t()
            {
                print 'hello';
            }

            $query = self::qb()
                ->select('*')
                ->from('users')
                ->as('t');

            $qq = self::qb()
                ->select($query);

            $case =
            [
                $qq,
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT (SELECT * FROM `users`) AS `t`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        // $cases['#4199 - forbids "/*", "*/" and "?" in hint comments'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        // $cases['#4199 - forbids non-strings as hint comments'] = function()
        // {
        //     // FIXME: Implement me!
        // };

        foreach ($cases as $name => $caseFn)
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

        $iQuery         = $iSharQCompiler->toQuery();
        $sqlAndBindings =
        [
            'sql'      => $iQuery->getSQL(),
            'bindings' => $iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}


