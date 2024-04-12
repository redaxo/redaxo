<?php

namespace Redaxo\Core\RexVar;

use Redaxo\Core\Core;

/**
 * REX_CONFIG[key=xzy].
 *
 * Arguments:
 *   - key
 *   - namespace
 */
class ConfigurationVar extends RexVar
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
