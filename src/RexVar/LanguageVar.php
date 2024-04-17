<?php

namespace Redaxo\Core\RexVar;

use Redaxo\Core\Language\Language;

/**
 * REX_LANGUAGE[id=x field=xzy].
 *
 * Arguments:
 *   - id
 *   - field
 */
class LanguageVar extends RexVar
{
    protected function getOutput()
    {
        $field = $this->getParsedArg('field', null, true);
        if (null === $field) {
            return false;
        }

        $id = $this->getParsedArg('id');
        $getMethod = $id ? 'get(' . $id . ')' : 'getCurrent()';

        return 'htmlspecialchars(\\' . Language::class . '::' . $getMethod . '->getValue(' . $field . '))';
    }
}
