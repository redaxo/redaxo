<?php
class rex_structure_action_priority extends rex_structure_action_base
{
    /**
     * @param int $article_rows
     * @return string
     * @throws rex_sql_exception
     */
    public function get($article_rows = 0)
    {
        if ($this->sql instanceof rex_sql) {
            $article_id = $this->sql->getValue('id');
            $article_priority = $this->sql->getValue('priority');
        } else {
            $article_id = 0;
            $article_priority = $article_rows + 1;
        }

        if (!$this->structure_context->hasCategoryPermission()) {
            return rex_escape($article_priority);
        }

        if ('add_art' == $this->structure_context->getFunction() && 0 == $article_id) {
            return '<input class="form-control" type="text" name="article-position" value="'.$article_priority.'" />';
        }

        if ('edit_art' == $this->structure_context->getFunction() && $article_id == $this->structure_context->getArticleId()) {
            return '<input class="form-control" type="text" name="article-position" value="'.rex_escape($article_priority).'" />';
        }

        return rex_escape($article_priority);
    }
}
