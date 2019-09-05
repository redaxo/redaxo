<?php
class rex_structure_field_name extends rex_structure_field_base
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
        } else {
            $article_id = 0;
            $article_name = '';
        }

        if (!$this->context->hasCategoryPermission()) {
            return $article_name;
        }

        if ('add_art' == $this->context->getFunction() && 0 == $article_id) {
            return '<input class="form-control" type="text" name="article-name" autofocus />';
        }

        if ('edit_art' == $this->context->getFunction() && $article_id == $this->context->getArticleId()) {
            return '<input class="form-control" type="text" name="article-name" value="'.rex_escape($article_name).'" autofocus />';
        }

        $url = $this->context->getContext()->getUrl([
            'page' => 'content/edit',
            'article_id' => $article_id,
            'mode' => 'edit',
        ]);

        return '<a href="'.$url.'">'.rex_escape($article_name).'</a>';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_article_name');
    }
}
