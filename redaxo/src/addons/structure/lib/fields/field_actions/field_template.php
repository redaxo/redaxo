<?php
class rex_structure_field_template extends rex_structure_field_base
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
            $template_id = $this->sql->getValue('template_id');
        } else {
            $article_id = 0;
            $template_id = 0;
        }

        // Plugin structure/content is not available
        if (!$this->context->hasTemplates()) {
            return '';
        }

        // Plugin structure/content is available
        $template_select = new rex_template_select($this->context->getCategoryId(), $this->context->getClangId());
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control selectpicker"');

        $template_name = $template_select->getTemplates();
        $template_name[0] = rex_i18n::msg('template_default_name');

        if ($this->context->hasCategoryPermission()) {
            // Add article
            if ('add_art' == $this->context->getFunction() && 0 == $article_id) {
                $template_select->setSelected();
                return $template_select->get();
            }

            // Edit article
            if ('edit_art' == $this->context->getFunction() && $article_id == $this->context->getArticleId()) {
                $template_select->setSelected($template_id);
                return $template_select->get();
            }
        }

        // Any other case
        return isset($template_name[$template_id]) ? $template_name[$template_id] : '';
    }

    /**
     * @return string
     */
    protected function getHeader()
    {
        return rex_i18n::msg('header_template');
    }
}
