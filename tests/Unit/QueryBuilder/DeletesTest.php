<?php


namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class DeletesTest extends \Codeception\Test\Unit
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

        $cases['delete method'] = function()
        {
            $case =
            [
                self::qb()
                    ->from('users')
                    ->where('email', '=', 'foo')
                    ->delete(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'DELETE FROM `users` WHERE `email` = ?',
                        'bindings' => ['foo']
                    ]
                ]
            ];

            return $case;
        };

        // $cases['delete only method'] = function()
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

        $cases['truncate method'] = function()
        {
            $case =
            [
                self::qb()
                    ->table('users')
                    ->truncate(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'TRUNCATE `users`',
                        'bindings' => []
                    ]
                ]
            ];

            return $case;
        };

        foreach ($cases as $name => $caseFn)
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

        $iQuery         = $iSharQCompiler->toQuery();
        $sqlAndBindings =
        [
            'sql'      => $iQuery->getSQL(),
            'bindings' => $iQuery->getBindings()
        ];

        $this->assertSame($iExpected['mysql'], $sqlAndBindings);
    }
}
