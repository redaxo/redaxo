<?php

use Redaxo\Core\RexVar\RexVar;

/**
 * REX_CLANG[id=x field=xzy].
 *
 * Arguments:
 *   - id
 *   - field
 */
class rex_var_clang extends RexVar
{
    protected function getOutput()
    {
        $field = $this->getParsedArg('field', null, true);
        if (null === $field) {
            return false;
        }

        $id = $this->getParsedArg('id');
        $getMethod = $id ? 'get(' . $id . ')' : 'getCurrent()';

        return 'htmlspecialchars(rex_clang::' . $getMethod . '->getValue(' . $field . '))';
    }
}
