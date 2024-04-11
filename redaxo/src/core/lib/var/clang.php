<?php

use Redaxo\Core\RexVar\AbstractRexVar;

/**
 * REX_CLANG[id=x field=xzy].
 *
 * Arguments:
 *   - id
 *   - field
 */
class rex_var_clang extends AbstractRexVar
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
