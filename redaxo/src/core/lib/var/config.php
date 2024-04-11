<?php

use Redaxo\Core\Core;
use Redaxo\Core\RexVar\AbstractRexVar;

/**
 * REX_CONFIG[key=xzy].
 *
 * Arguments:
 *   - key
 *   - namespace
 */
class rex_var_config extends AbstractRexVar
{
    protected function getOutput()
    {
        $key = $this->getParsedArg('key', null, true);
        if (null === $key) {
            return false;
        }
        $namespace = $this->getParsedArg('namespace', "'" . Core::CONFIG_NAMESPACE . "'");
        return 'htmlspecialchars(rex_config::get(' . $namespace . ', ' . $key . '))';
    }
}
