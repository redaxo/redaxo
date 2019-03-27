<?php

use PHPUnit\Framework\TestFailure;
use PHPUnit\TextUI\ResultPrinter;
use PHPUnit\Util\Filter;

/**
 * @package redaxo\tests
 *
 * @internal
 */
class rex_tests_result_printer extends ResultPrinter
{
    protected $backtrace;

    public function __construct($backtrace, $colors = ResultPrinter::COLOR_DEFAULT)
    {
        $out = null;
        if (PHP_SAPI == 'cli') {
            // prevent headers already sent error when started from CLI
            $out = 'php://stderr';
        }

        parent::__construct($out, false, $colors);

        $this->backtrace = '';
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                $this->backtrace .= $trace['file'] . ':' . $trace['line'] . "\n";
            }
        }
    }

    protected function printDefectTrace(TestFailure $defect): void
    {
        $stacktrace = Filter::getFilteredStacktrace($defect->thrownException());

        $stacktrace = str_replace([$this->backtrace, rex_path::base()], '', $stacktrace);

        $this->write($defect->getExceptionAsString() . "\n" . $stacktrace);
    }
}
