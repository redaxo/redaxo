<?php

namespace Redaxo\Core\Tests\RexVar;

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\File;
use Redaxo\Core\RexVar\RexVar;
use Redaxo\Core\Util\Stream;

/** @internal */
abstract class RexVarTestBase extends TestCase
{
    protected function getParseOutput(string $content): string
    {
        return File::getOutput(Stream::factory('rex-var-test', RexVar::parse($content)));
    }

    protected function assertParseOutputEquals(string $expected, string $content, string $msg = 'Parsed content has not expected output.'): void
    {
        self::assertEquals($expected, $this->getParseOutput($content), $msg);
    }
}
