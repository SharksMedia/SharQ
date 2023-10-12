<?php

namespace Tests\Unit;

// use Tests\Support\;
use Sharksmedia\SharQ\SharQ;
use Sharksmedia\SharQ\Client\MySQL;
use Sharksmedia\SharQ\Config;

use Sharksmedia\SharQ\SharQCompiler;
use Sharksmedia\SharQ\Statement\Raw;

class LocksTest extends \Codeception\Test\Unit
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

        $cases['lock for update'] = function()
        {
            $case =
            [
                self::qb()
                    ->select('*')
                    ->from('foo')
                    ->where('bar', '=', 'baz')
                    ->forUpdate(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` WHERE `bar` = ? FOR UPDATE',
                        'bindings' => ['baz']
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
                    ->from('foo')
                    ->where('bar', '=', 'baz')
                    ->forShare(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` WHERE `bar` = ? LOCK IN SHARE MODE',
                        'bindings' => ['baz']
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
                    ->from('foo')
                    ->where('bar', '=', 'baz')
                    ->forUpdate(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` WHERE `bar` = ? FOR UPDATE',
                        'bindings' => ['baz']
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
                    ->from('foo')
                    ->where('bar', '=', 'baz')
                    ->forUpdate('lo', 'rem'),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` WHERE `bar` = ? FOR UPDATE',
                        'bindings' => ['baz']
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
                    ->from('foo')
                    ->where('bar', '=', 'baz')
                    ->forUpdate(['lo', 'rem']),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` WHERE `bar` = ? FOR UPDATE',
                        'bindings' => ['baz']
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
                    ->from('foo')
                    ->first()
                    ->forUpdate()
                    ->skipLocked(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` LIMIT ? FOR UPDATE SKIP LOCKED',
                        'bindings' => [1]
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
                    ->from('foo')
                    ->first()
                    ->forUpdate()
                    ->noWait(),
                [
                    'mysql' =>
                    [
                        'sql'      => 'SELECT * FROM `foo` LIMIT ? FOR UPDATE NOWAIT',
                        'bindings' => [1]
                    ]
                ]
            ];

            return $case;
        };

        // $cases['noWait and skipLocked require a lock mode to be set'] = function()
        // {

        //   it('noWait and skipLocked require a lock mode to be set', () => {
        //     expect(() => {
        //       qb().select('*').noWait().toString();
        //     }).to.throw(
        //       '.noWait() can only be used after a call to .forShare() or .forUpdate()!'
        //     );
        //     expect(() => {
        //       qb().select('*').skipLocked().toString();
        //     }).to.throw(
        //       '.skipLocked() can only be used after a call to .forShare() or .forUpdate()!'
        //     );
        //   });
        // };

        // $cases['skipLocked conflicts with noWait and vice-versa'] = function()
        // {
        //
        //   it('skipLocked conflicts with noWait and vice-versa', () => {
        //     expect(() => {
        //       qb().select('*').forUpdate().noWait().skipLocked().toString();
        //     }).to.throw('.skipLocked() cannot be used together with .noWait()!');
        //     expect(() => {
        //       qb().select('*').forUpdate().skipLocked().noWait().toString();
        //     }).to.throw('.noWait() cannot be used together with .skipLocked()!');
        //   });
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

