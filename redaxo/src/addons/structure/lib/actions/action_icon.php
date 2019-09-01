<?php
class rex_structure_action_icon extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql->getValue('id') == rex_article::getSiteStartArticleId()) {
            $class = ' rex-icon-sitestartarticle';
        } elseif (1 == $this->sql->getValue('startarticle')) {
            $class = ' rex-icon-startarticle';
        } else {
            $class = ' rex-icon-article';
        }

        $icon = '<i class="rex-icon'.$class.'"></i>';

        if (!$this->structure_context->hasCategoryPermission()) {
            return $icon;
        }

        $name = rex_escape($this->sql->getValue('name'));
        $url = $this->structure_context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $this->sql->getValue('id'),
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'" title="'.$name.'"><i class="rex-icon'.$class.'"></i></a>';
    }
}
