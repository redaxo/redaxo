<?php

rex_extension::register('OUTPUT_FILTER', ['rex_sql_debug', 'doLog']);

/**
 * Class to monitor sql queries.
 *
 * @author staabm
 *
 * @package redaxo\debug
 */
class rex_sql_debug extends rex_sql
{
    private static $queries = [];
    private static $errors = 0;

    /**
     * {@inheritdoc}.
     */
    public function setQuery($qry, array $params = [], array $options = [])
    {
        try {
            parent::setQuery($qry, $params, $options);
        } catch (rex_exception $e) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $file = $trace[0]['file'];
            $line = $trace[0]['line'];
            for ($i = 1; $i < count($trace); ++$i) {
                if (isset($trace[$i]['file']) && false === strpos($trace[$i]['file'], 'sql.php')) {
                    $file = $trace[$i]['file'];
                    $line = $trace[$i]['line'];
                    break;
                }
            }
            ChromePhp::error($e->getMessage() . ' in ' . $file . ' on line ' . $line);
            throw $e; // re-throw exception after logging
        }

        return $this;
    }

    /**
     * {@inheritdoc}.
     */
    // TODO queries using setQuery() are not logged yet!
    public function execute(array $params = [], array $options = [])
    {
        $qry = $this->stmt->queryString;

        $timer = new rex_timer();
        parent::execute($params, $options);

        $err = $errno = '';
        if ($this->hasError()) {
            ++self::$errors;
            $err = parent::getError();
            $errno = parent::getErrno();
        }

        self::$queries[] = [
            'rows' => $this->getRows(),
            'time' => $timer->getFormattedDelta(),
            'query' => $qry,
            'error' => $err,
            'errno' => $errno,
        ];

        return $this;
    }

    public static function doLog()
    {
        if (!empty(self::$queries)) {
            $tbl = [];
            $i = 0;

            foreach (self::$queries as $qry) {
                // when a extension takes longer than 5ms, send a warning
                if (strtr($qry['time'], ',', '.') > 5) {
                    $tbl[] = ['#' => $i, 'rows' => $qry['rows'], 'ms' => '! SLOW: ' . $qry['time'], 'query' => $qry['query']];
                } else {
                    $tbl[] = ['#' => $i, 'rows' => $qry['rows'], 'ms' => $qry['time'], 'query' => $qry['query']];
                }
                ++$i;
            }

            ChromePhp::log(self::class . ' (' . count(self::$queries) . ' queries, ' . self::$errors . ' errors)');
            ChromePhp::table($tbl);
        }
    }
}
