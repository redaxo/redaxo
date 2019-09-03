<?php
class rex_structure_action_icon extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
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

        if (!$this->structure_context->hasCategoryPermission() || 0 == $article_id) {
            return $icon;
        }

        $url = $this->structure_context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $article_id,
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'" title="'.rex_escape($article_name).'"><i class="rex-icon'.$class.'"></i></a>';
    }
}
