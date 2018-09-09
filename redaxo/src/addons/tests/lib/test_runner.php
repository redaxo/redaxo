<?php

class_alias('PHPUnit_Framework_TestCase', '\PHPUnit\Framework\TestCase');

/**
 * @package redaxo\tests
 *
 * @internal
 */
class rex_test_runner
{
    public function setUp()
    {
        // nothing todo, method kept for BC
    }

    public function run(rex_test_locator $locator, $colors = PHPUnit_TextUI_ResultPrinter::COLOR_DEFAULT)
    {
        $suite = new PHPUnit_Framework_TestSuite();
        // disable backup of globals, since we have some rex_sql objectes referenced from variables in global space.
        // PDOStatements are not allowed to be serialized
        $suite->setBackupGlobals(false);
        $suite->addTestFiles($locator->getIterator());

        rex_error_handler::unregister();

        $runner = new PHPUnit_TextUI_TestRunner();

        $backtrace = debug_backtrace(false);
        array_unshift($backtrace, ['file' => __FILE__, 'line' => __LINE__ + 3]);
        $runner->setPrinter(new rex_tests_result_printer($backtrace, $colors));

        $result = $runner->doRun($suite);

        rex_error_handler::register();

        return $result;
    }
}
