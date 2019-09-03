<?php
class rex_structure_action_name extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql instanceof rex_sql) {
            $article_id = $this->sql->getValue('id');
            $article_name = $this->sql->getValue('name');
        } else {
            $article_id = 0;
            $article_name = '';
        }

        if (!$this->structure_context->hasCategoryPermission()) {
            return $article_name;
        }

        if ('add_art' == $this->structure_context->getFunction() && 0 == $article_id) {
            return '<input class="form-control" type="text" name="article-name" autofocus />';
        }

        if ('edit_art' == $this->structure_context->getFunction() && $article_id == $this->structure_context->getArticleId()) {
            return '<input class="form-control" type="text" name="article-name" value="'.rex_escape($article_name).'" autofocus />';
        }

        $url = $this->structure_context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $article_id,
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'">'.rex_escape($article_name).'</a>';
    }
}
