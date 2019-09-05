<?php
class rex_structure_field_status extends rex_structure_field_base
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
        $article_status_types = rex_article_service::statusTypes();
        $article_status = $article_status_types[$this->sql->getValue('status')][0];
        $article_class = $article_status_types[$this->sql->getValue('status')][1];
        $article_icon = $article_status_types[$this->sql->getValue('status')][2];

        $status = '<i class="rex-icon '.$article_icon.'"></i> '.$article_status;

        if ($this->sql->getValue('startarticle') == 1 || !rex::getUser()->hasPerm('publishArticle[]') || !$this->context->hasCategoryPermission()) {
            return '<span class="'.$article_class.' text-muted">'.$status.'</span>';
        }

        $url = $this->context->getContext()->getUrl([
            'article_id' => $this->sql->getValue('id'),
            'artstart' => $this->context->getArtStart(),
        ] + rex_api_article_status::getUrlParams());

        return '<a class="'.$article_class.'" href="'.$url.'">'.$status.'</a>';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_status');
    }
}
