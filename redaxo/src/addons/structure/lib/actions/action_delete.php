<?php
class rex_structure_action_delete extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $delete = '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete');

        if ($this->sql->getValue('startarticle') == 1 || !$this->structure_context->hasCategoryPermission()) {
            return '<span class="text-muted">'.$delete.'</span>';
        }

        $url = $this->structure_context->getContext()->getUrl([
            'article_id' => $this->sql->getValue('id'),
            'artstart' => $this->structure_context->getArtStart()
        ] + rex_api_article_delete::getUrlParams());

        return '<a href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').'?">'.$delete.'</a>';
    }
}
