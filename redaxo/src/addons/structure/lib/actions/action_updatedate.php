<?php
class rex_structure_action_updatedate extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql instanceof rex_sql) {
            $updatedate = rex_formatter::strftime($this->sql->getDateTimeValue('updatedate'), 'date');
        } else {
            $updatedate = rex_formatter::strftime(time(), 'date');
        }

        if (!$this->structure_context->hasCategoryPermission()) {
            return $updatedate;
        }

        return $updatedate;
    }
}
