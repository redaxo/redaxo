<?php

/**
 * REX_PROPERTY[key=xzy].
 *
 * Arguments:
 *   - key
 *   - namespace
 */
class rex_var_property extends rex_var
{
    protected function getOutput()
    {
        $key = $this->getParsedArg('key', null, true);
        if (null === $key) {
            return false;
        }
        $namespace = $this->getParsedArg('namespace');
        $base = $namespace ? rex_addon::class . '::get(' . $namespace . ')->' : rex::class . '::';
        return 'htmlspecialchars(' . $base . 'getProperty(' . $key . '))';
    }
}
