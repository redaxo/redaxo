<?php

/**
 * @package redaxo\tests
 */
class rex_tests_result_printer extends PHPUnit_TextUI_ResultPrinter
{
    protected $backtrace;

    public function __construct($backtrace, $colors = false)
    {
        parent::__construct(null, false, $colors);

        $this->backtrace = '';
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                $this->backtrace .= $trace['file'] . ':' . $trace['line'] . "\n";
            }
        }
    }

    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        $stacktrace = PHPUnit_Util_Filter::getFilteredStacktrace($defect->thrownException());

        $stacktrace = str_replace([$this->backtrace, rex_path::base()], '', $stacktrace);

        $this->write($defect->getExceptionAsString() . "\n" . $stacktrace);
    }
}
