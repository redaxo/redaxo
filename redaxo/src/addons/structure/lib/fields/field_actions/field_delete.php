<?php
class rex_structure_field_delete extends rex_structure_field_base
{
    /**
     * @return string
     */
    public function get()
    {
        switch ($this->type) {
            case rex_structure_field_group::HEADER:
                return $this->getHeader();
                break;

            case rex_structure_field_group::BODY:
            default:
                return $this->getBody();

        }
    }

    /**
     * @return string
     */
    protected function getBody()
    {
        $delete = '<i class="rex-icon rex-icon-delete"></i> '.rex_i18n::msg('delete');

        if ($this->sql->getValue('startarticle') == 1 || !$this->context->hasCategoryPermission()) {
            return '<span class="text-muted">'.$delete.'</span>';
        }

        $url = $this->context->getContext()->getUrl([
            'article_id' => $this->sql->getValue('id'),
            'artstart' => $this->context->getArtStart()
        ] + rex_api_article_delete::getUrlParams());

        return '<a href="'.$url.'" data-confirm="'.rex_i18n::msg('delete').'?">'.$delete.'</a>';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_status');
    }
}
