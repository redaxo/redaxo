<?php
class rex_structure_action_id extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql instanceof rex_sql) {
            $id = $this->sql->getValue('id');
        } else {
            $id = '-';
        }

        if (!$this->structure_context->hasCategoryPermission()) {
            return $id;
        }

        return $id;
    }
}
