<?php
class rex_structure_action_change extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $change = '<i class="rex-icon rex-icon-edit"></i> '.rex_i18n::msg('change');

        if (!$this->structure_context->hasCategoryPermission()) {
            return '<span class="text-muted">'.$change.'</span>';
        }

        $url = $this->structure_context->getContext()->getUrl([
            'article_id' => $this->sql->getValue('id'),
            'function' => 'edit_art',
            'artstart' => $this->structure_context->getArtStart(),
        ]);

        return '<a href="'.$url.'">'.$change.'</a>';
    }
}
