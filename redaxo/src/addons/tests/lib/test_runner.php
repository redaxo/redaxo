<?php

use mheap\GithubActionsReporter\Printer;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\TextUI\TestRunner;

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

    public function run(rex_test_locator $locator, $colors = ResultPrinter::COLOR_DEFAULT)
    {
        $suite = new TestSuite();
        // disable backup of globals, since we have some rex_sql objectes referenced from variables in global space.
        // PDOStatements are not allowed to be serialized
        $suite->setBackupGlobals(false);
        $suite->addTestFiles($locator->getIterator());

        rex_error_handler::unregister();

        $runner = new TestRunner();

        $backtrace = debug_backtrace(false);
        array_unshift($backtrace, ['file' => __FILE__, 'line' => __LINE__ + 3]);

        // use different result printer with github actions checks integration
        if (getenv('GITHUB_ACTIONS')) {
            echo "Running in Github Actions\n";
            $runner->setPrinter(new Printer(null, false, $colors));
        } else {
            $runner->setPrinter(new rex_tests_result_printer($backtrace, $colors));
        }

        $result = $runner->doRun($suite);

        rex_error_handler::register();

        return $result;
    }
}
