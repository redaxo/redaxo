<?php
/**
 * Whoops - php errors for cool kids
 * @author Filipe Dobreira <http://github.com/filp>
 */

namespace Whoops\Handler;

use Whoops\Exception\Inspector;
use Whoops\RunInterface;

interface HandlerInterface
{
    /**
     * @return int|null A handler may return nothing, or a Handler::HANDLE_* constant
     */
    public function handle();

    /**
     * @param  RunInterface  $run
     *
     */
    public function setRun(RunInterface $run);

    /**
     * @param  \Throwable $exception
     *
     */
    public function setException($exception);

    /**
     * @param  Inspector $inspector
     *
     */
    public function setInspector(Inspector $inspector);
}
