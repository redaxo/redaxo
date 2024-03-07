<?php

use PHPUnit\Framework\TestCase;
use Redaxo\Core\Filesystem\File;

abstract class rex_var_test_base extends TestCase
{
    protected function getParseOutput(string $content): string
    {
        return File::getOutput(rex_stream::factory('rex-var-test', rex_var::parse($content)));
    }

    protected function assertParseOutputEquals(string $expected, string $content, string $msg = 'Parsed content has not expected output.'): void
    {
        self::assertEquals($expected, $this->getParseOutput($content), $msg);
    }
}
