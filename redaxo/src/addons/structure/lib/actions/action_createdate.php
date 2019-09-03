<?php
class rex_structure_action_createdate extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql instanceof rex_sql) {
            $createdate = rex_formatter::strftime($this->sql->getDateTimeValue('createdate'), 'date');
        } else {
            $createdate = rex_formatter::strftime(time(), 'date');
        }

        if (!$this->structure_context->hasCategoryPermission()) {
            return $createdate;
        }

        return $createdate;
    }
}
