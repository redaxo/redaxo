<?php

namespace Redaxo\Core\RexVar;

use Redaxo\Core\Addon\Addon;
use Redaxo\Core\Core;

/**
 * REX_PROPERTY[key=xzy].
 *
 * Arguments:
 *   - key
 *   - namespace
 */
class Property extends AbstractRexVar
{
    protected function getOutput()
    {
        $key = $this->getParsedArg('key', null, true);
        if (null === $key) {
            return false;
        }
        $namespace = $this->getParsedArg('namespace');
        $base = $namespace ? Addon::class . '::get(' . $namespace . ')->' : Core::class . '::';
        return 'htmlspecialchars(' . $base . 'getProperty(' . $key . '))';
    }
}
