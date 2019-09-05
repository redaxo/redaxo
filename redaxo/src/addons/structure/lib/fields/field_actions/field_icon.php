<?php
class rex_structure_field_icon extends rex_structure_field_base
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
        if ($this->sql instanceof rex_sql) {
            $article_id = $this->sql->getValue('id');
            $article_name = $this->sql->getValue('name');
            $is_startarticle = $this->sql->getValue('startarticle');
        } else {
            $article_id = 0;
            $article_name = '';
            $is_startarticle = 0;
        }

        if ($article_id == rex_article::getSiteStartArticleId()) {
            $class = ' rex-icon-sitestartarticle';
        } elseif (1 == $is_startarticle) {
            $class = ' rex-icon-startarticle';
        } else {
            $class = ' rex-icon-article';
        }

        $icon = '<i class="rex-icon'.$class.'"></i>';

        if (!$this->context->hasCategoryPermission() || 0 == $article_id) {
            return $icon;
        }

        $url = $this->context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $article_id,
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'" title="'.rex_escape($article_name).'"><i class="rex-icon'.$class.'"></i></a>';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        if (!$this->context->hasCategoryPermission()) {
            return '';
        }

        $url = $this->context->getContext()->getUrl([
            'function' => 'add_art',
            'artstart' => $this->context->getArtStart()
        ]);

        return '<a href="'.$url.'" '.rex::getAccesskey(rex_i18n::msg('article_add'), 'add_2').'><i class="rex-icon rex-icon-add-article"></i></a>';
    }
}
