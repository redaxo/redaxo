<?php

/**
 * @package redaxo\debug
 *
 * @internal
 */
class rex_sql_debug extends rex_sql
{
    public function setQuery($query, array $params = [], array $options = [])
    {
        try {
            $timer = new rex_timer();
            parent::setQuery($query, $params, $options);

            // to prevent double entries, log only if no params are passed
            if (empty($params)) {
                rex_debug_clockwork::getInstance()->getRequest()
                    ->addDatabaseQuery($query, $params, $timer->getDelta(), ['connection' => $this->DBID] + rex_debug::getTrace());
            }
        } catch (rex_exception $e) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $file = $trace[0]['file'];
            $line = $trace[0]['line'];
            for ($i = 1; $i < count($trace); ++$i) {
                if (isset($trace[$i]['file']) && !str_contains($trace[$i]['file'], 'sql.php')) {
                    $file = $trace[$i]['file'];
                    $line = $trace[$i]['line'];
                    break;
                }
            }
            rex_debug_clockwork::getInstance()
                ->log('error', $e->getMessage(), ['file' => $file, 'line' => $line]);
            throw $e; // re-throw exception after logging
        }

        return $this;
    }

    public function execute(array $params = [], array $options = [])
    {
        assert($this->stmt instanceof PDOStatement);
        $qry = $this->stmt->queryString;

        $timer = new rex_timer();
        parent::execute($params, $options);

        rex_debug_clockwork::getInstance()->getRequest()
            ->addDatabaseQuery($qry, $params, $timer->getDelta(), ['connection' => $this->DBID] + rex_debug::getTrace());

        return $this;
    }
}
