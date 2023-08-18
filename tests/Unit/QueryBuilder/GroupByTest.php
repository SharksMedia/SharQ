<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class GroupByTest extends \Codeception\Test\Unit
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

    private static function qb(): SharQ
    {// 2023-05-16
        $iClient = self::getClient();

        return new SharQ($iClient, 'my_schema');
    }

    public function caseProvider()
    {// 2023-05-16
        $cases = [];

        $cases['group bys'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('users')
                    ->groupBy('id', 'email'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` GROUP BY `id`, `email`',
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
                    ->from('users')
                    ->groupByRaw('id', 'email'),
                [
                    'mysql'=>
                    [
                        'sql'=>'SELECT * FROM `users` GROUP BY id, email',
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

