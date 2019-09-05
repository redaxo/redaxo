<?php
class rex_structure_field_priority extends rex_structure_field_base
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
            $article_priority = $this->sql->getValue('priority');
        } else {
            $article_id = 0;
            $article_priority = $article_rows + 1;
        }

        if (!$this->context->hasCategoryPermission()) {
            return rex_escape($article_priority);
        }

        if ('add_art' == $this->context->getFunction() && 0 == $article_id) {
            return '<input class="form-control" type="text" name="article-position" value="'.$article_priority.'" />';
        }

        if ('edit_art' == $this->context->getFunction() && $article_id == $this->context->getArticleId()) {
            return '<input class="form-control" type="text" name="article-position" value="'.rex_escape($article_priority).'" />';
        }

        return rex_escape($article_priority);
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_priority');
    }
}
