<?php
declare(strict_types=1);

namespace PhpMyAdmin\SqlParser\Tests\Parser;

use PhpMyAdmin\SqlParser\Tests\TestCase;

class LockStatementTest extends TestCase
{
    /**
     * @param mixed $test
     *
     * @dataProvider lockProvider
     */
    public function testLock($test)
    {
        $this->runParserTest($test);
    }

    public function lockProvider()
    {
        return [
            ['parser/parseLock1'],
            ['parser/parseLock2'],
            ['parser/parseLock3'],
            ['parser/parseLock4'],
            ['parser/parseLock5'],
            ['parser/parseLockErr1'],
            ['parser/parseLockErr2'],
            ['parser/parseLockErr3'],
            ['parser/parseLockErr4'],
            ['parser/parseLockErr5'],
            ['parser/parseLockErr6'],
            ['parser/parseLockErr7'],
            ['parser/parseLockErr8'],
            ['parser/parseLockErr9'],
            ['parser/parseLockErr10'],
            ['parser/parseUnlock1'],
            ['parser/parseUnlockErr1'],
        ];
    }
}
