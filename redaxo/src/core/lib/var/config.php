<?php

/**
 * REX_CONFIG[key=xzy].
 *
 * Arguments:
 *   - key
 *   - namespace
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_var_config extends rex_var
{
    protected function getOutput()
    {
        $key = $this->getParsedArg('key', null, true);
        if (null === $key) {
            return false;
        }
        $namespace = $this->getParsedArg('namespace', "'" . rex::CONFIG_NAMESPACE . "'");
        return 'htmlspecialchars(rex_config::get(' . $namespace . ', ' . $key . '))';
    }
}
