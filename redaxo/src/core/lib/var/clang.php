<?php

/**
 * REX_CLANG[id=x field=xzy].
 *
 * Arguments:
 *   - id
 *   - field
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_var_clang extends rex_var
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
