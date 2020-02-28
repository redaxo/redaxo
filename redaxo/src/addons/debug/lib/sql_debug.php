<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_sql_debug extends rex_sql
{
    /**
     * {@inheritdoc}.
     */
    public function setQuery($qry, array $params = [], array $options = [])
    {
        try {
            $timer = new rex_timer();
            parent::setQuery($qry, $params, $options);
            rex_debug::getInstance()
                ->addDatabaseQuery($qry, $params, $timer->getDelta(), ['connection' => $this->DBID] + rex_debug::getTrace());
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
            rex_debug::getInstance()
                ->error($e->getMessage(), ['file' => $file, 'line' => $line]);
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

        rex_debug::getInstance()
            ->addDatabaseQuery($qry, $params, $timer->getDelta(), ['connection' => $this->DBID] + rex_debug::getTrace());

        $err = $errno = '';
        if ($this->hasError()) {
            $err = parent::getError();
            $errno = parent::getErrno();
        }

        rex_debug::getInstance()
            ->addDatabaseQuery($qry, $params, $timer->getDelta(), [
                'rows' => $this->getRows(),
                'time' => $timer->getFormattedDelta(),
                'query' => $qry,
                'error' => $err,
                'errno' => $errno,
            ]);

        return $this;
    }
}
