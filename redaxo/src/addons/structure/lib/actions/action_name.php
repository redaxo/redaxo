<?php
class rex_structure_action_name extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $name = rex_escape($this->sql->getValue('name'));

        if (!$this->structure_context->hasCategoryPermission()) {
            return $name;
        }

        $url = $this->structure_context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $this->sql->getValue('id'),
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'">'.$name.'</a>';
    }
}
