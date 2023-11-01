<?php


namespace Tests\Unit;

class MiscTest extends \Codeception\Test\Unit
{
    use \Tests\TQueryBuilderUnitTest;

    public function testFullSubSelects()
    {
        $qb = self::qb()
            ->select('email')
            ->from('users')
            ->where('email', '=', 'foo')
            ->orWhere('id', '=', function($q)
            {
                $q->select(self::raw('MAX(id)'))
                    ->from('users')
                    ->where('email', '=', 'bar');
            });

        $expected = [
            'mysql' => [
                'sql'      => 'SELECT `email` FROM `users` WHERE `email` = ? OR `id` = (SELECT MAX(id) FROM `users` WHERE `email` = ?)',
                'bindings' => ['foo', 'bar']
            ]
        ];

        $this->_testSharQ($qb, $expected);
    }

    public function testClearNestedSelects()
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

        $this->_testSharQ(...$case);
    }

    public function testClearNonNestedSelects()
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

        $this->_testSharQ(...$case);
    }

    public function testSupportsArbitrarilyNestedRaws()
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

        $this->_testSharQ(...$case);
    }

    public function test4199AllowsAnHintComment()
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

        $this->_testSharQ(...$case);
    }

    public function test4199AllowsMultipleHintComments()
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

        $this->_testSharQ(...$case);
    }

    public function test1982ShouldAllowQueryCommentsInQuerybuilder()
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

        $this->_testSharQ(...$case);
    }

    public function test4199AllowsHintCommentsInSubqueries()
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

        $this->_testSharQ(...$case);
    }

    public function test4199AllowsHintCommentsInUnions()
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

        $this->_testSharQ(...$case);
    }

    public function testHasAModifyMethodWhichAcceptsAFunctionThatCanModifyTheQuery()
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

        $this->_testSharQ(...$case);
    }

    public function testMed24GetContentDrugApprovals()
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

        $this->_testSharQ(...$case);
    }

    public function testMed24GetContentDrugApprovals2()
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

        $this->_testSharQ(...$case);
    }

    public function testSerialize()
    {
        $query = self::qb()
            ->select('*')
            ->from('users')
            ->as('t');

        $serialized = serialize($query);

        $unserializedQuery = unserialize($serialized);

        $unserializedQuery->setClient($query->getClient());

        $this->assertEquals($query, $unserializedQuery);
    }

    public function testIdentifier()
    {
        $query = self::qb()
            ->select('*')
            ->from('users')
            ->leftJoin('a', 'a.id', '=', 'p.userID')->identify('a')
            ->join('p', 'users.userID', '=', 'p.userID');

        $this->assertTrue($query->hasIdentifier('a'));
        $this->assertFalse($query->hasIdentifier('b'));

        $case =
        [
            $query,
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

    public function testClearingWithIdentifier()
    {
        $query = self::qb()
            ->select('*')
            ->from('users')
            ->leftJoin('a', 'a.id', '=', 'p.userID')->identify('a')
            ->join('p', 'users.userID', '=', 'p.userID');

        $query->clearWithIdentifier('a');

        $case =
        [
            $query,
            [
                'mysql' =>
                [
                    'sql'      => 'SELECT * FROM `users` INNER JOIN `p` ON(`users`.`userID` = `p`.`userID`)',
                    'bindings' => []
                ]
            ]
        ];

        $this->_testSharQ(...$case);
    }
}
