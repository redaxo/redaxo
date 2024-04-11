<?php

namespace Redaxo\Core\Content\RexVar;

use Redaxo\Core\RexVar\RexVar;

use function in_array;

/**
 * REX_VALUE[1],.
 */
class Value extends RexVar
{
    protected function getOutput()
    {
        $id = $this->getArg('id', 0, true);
        if (!in_array($this->getContext(), ['module', 'action']) || !is_numeric($id) || $id < 1 || $id > 20) {
            return false;
        }

        $value = $this->getContextData()->getValue('value' . $id);

        if ($this->hasArg('isset') && $this->getArg('isset')) {
            return $value ? 'true' : 'false';
        }

        if (!isset($value)) {
            $value = '';
        }

        $output = $this->getArg('output');
        if ('html' == $output) {
            $value = str_replace(['<?', '?>'], ['&lt;?', '?&gt;'], $value);
        } else {
            $value = rex_escape($value);
            if (!$this->environmentIs(self::ENV_INPUT)) {
                $value = nl2br($value);
            }
        }

        return self::quote($value);
    }
}
