<?php
class rex_structure_action_updatedate extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $updatedate = rex_formatter::strftime($this->sql->getDateTimeValue('updatedate'), 'date');

        if (!$this->structure_context->hasCategoryPermission()) {
            return $updatedate;
        }

        return $updatedate;
    }
}
