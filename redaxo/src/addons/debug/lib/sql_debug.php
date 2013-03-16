<?php

rex_extension::register('OUTPUT_FILTER', ['rex_sql_debug', 'doLog']);

/**
 * Class to monitor sql queries
 *
 * @author staabm
 * @package redaxo\debug
 */
class rex_sql_debug extends rex_sql
{
    private static $queries = [];
    private static $errors  = 0;

    public function setQuery($qry, array $params = [])
    {
        try {
            parent::setQuery($qry, $params);
        } catch (rex_exception $e) {
            $trace = debug_backtrace();
            for ( $i = 0 ; $trace && $i < sizeof($trace) ; $i++ ) {
                    if (isset($trace[$i]['file']) && strpos($trace[$i]['file'], 'sql.php') === false) {
                            $file = $trace[$i]['file'];
                            $line = $trace[$i]['line'];
                            break;
                    }
            }
            $firephp = FirePHP::getInstance(true);
            $firephp->error($e->getMessage() . ' in ' . $file . ' on line ' . $line);
            throw $e; // re-throw exception after logging
        }

        return $this;
    }

    // TODO queries using setQuery() are not logged yet!
    public function execute(array $params = [])
    {
        $qry   = $this->stmt->queryString;

        $timer = new rex_timer();
        parent::execute($params);

        $err = $errno = '';
        if ($this->hasError()) {
            self::$errors++;
            $err   = parent::getError();
            $errno = parent::getErrno();
        }

        self::$queries[] = [
            'rows'  => $this->getRows(),
            'time'  => $timer->getFormattedDelta(),
            'query' => $qry,
            'error' => $err,
            'errno' => $errno
        ];

        return $this;
    }

    public static function doLog()
    {
        if (!empty(self::$queries)) {
            $tbl = [];
            $tbl[] = ['#', 'rows', 'ms', 'query'];
            $i = 0;

            foreach (self::$queries as $qry) {
                // when a extension takes longer than 5ms, send a warning
                if (strtr($qry['time'], ',', '.') > 5) {
                    $tbl[] = [$i, $qry['rows'], '! SLOW: ' . $qry['time'], $qry['query']];
                } else {
                    $tbl[] = [$i, $qry['rows'], $qry['time'], $qry['query']];
                }
                $i++;
            }

            $firephp = FirePHP::getInstance(true);
            $firephp->table(__CLASS__ . ' (' . count(self::$queries) . ' queries, ' . self::$errors . ' errors)', $tbl);
        }
    }
}
