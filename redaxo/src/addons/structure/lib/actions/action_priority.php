<?php
class rex_structure_action_priority extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $priority = rex_escape($this->sql->getValue('priority'));

        if (!$this->structure_context->hasCategoryPermission()) {
            return $priority;
        }

        return $priority;
    }
}
