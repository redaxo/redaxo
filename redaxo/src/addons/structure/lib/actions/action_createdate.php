<?php
class rex_structure_action_createdate extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $createdate = rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');

        if (!$this->structure_context->hasCategoryPermission()) {
            return $createdate;
        }

        return $createdate;
    }
}
