<?php


namespace Tests\Unit;

// use Tests\Support\;

use PHPUnit\Framework\ExpectationFailedException;
use Sharksmedia\SharQ\SharQ;

use Sharksmedia\SharQ\SharQCompiler;

class DeletesTest extends \Codeception\Test\Unit
{
    use \Tests\Support\TQueryBuilderUnitTest;

    /**
     * @var \UnitTester
     */
    protected $tester;

    public function testSimpleDelete(): void
    {
        $qb = self::qb()
            ->delete()
            ->from('users')
            ->where('email', '=', 'foo');

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'DELETE `users` FROM `users` WHERE `email` = ?',
                'bindings' => ['foo']
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    public function testSimpleTruncate(): void
    {
        $qb = self::qb()
            ->table('users')
            ->truncate();

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'TRUNCATE `users`',
                'bindings' => []
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    public function testDeleteWithJoin(): void
    {
        $qb = self::qb()
            ->delete()
            ->from('users')
            ->join('contacts', 'users.id', '=', 'contacts.id')
            ->where('email', '=', 'foo');

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'DELETE `users` FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) WHERE `email` = ?',
                'bindings' => ['foo']
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    public function testDeleteMultipleWithJoinFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('When deleting from multiple tables, a table must be provided');

        $qb = self::qb()
            ->delete(['users', 'contacts'])
            ->join('contacts', 'users.id', '=', 'contacts.id')
            ->where('email', '=', 'foo');

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'DELETE `users`, `contacts` FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) WHERE `email` = ?',
                'bindings' => ['foo']
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    public function testDeleteMultipleWithJoin(): void
    {
        $qb = self::qb()
            ->delete(['users', 'contacts'])
            ->from('users')
            ->join('contacts', 'users.id', '=', 'contacts.id')
            ->where('email', '=', 'foo');

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'DELETE `users`, `contacts` FROM `users` INNER JOIN `contacts` ON(`users`.`id` = `contacts`.`id`) WHERE `email` = ?',
                'bindings' => ['foo']
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    public function testDeleteAll(): void
    {
        $qb = self::qb()
            ->delete()
            ->from('users');

        $sqlDialiects =
        [
            'mysql' =>
            [
                'sql'      => 'DELETE `users` FROM `users`',
                'bindings' => []
            ]
        ];

        $this->_testSharQ($qb, $sqlDialiects);
    }

    /**
     * @param SharQ $iSharQ
     * @param array $iExpected
     * @return void
     * @throws ExpectationFailedException
     */
    public function _testSharQ(SharQ $iSharQ, array $iExpected): void
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
