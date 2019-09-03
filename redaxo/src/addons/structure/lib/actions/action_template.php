<?php
class rex_structure_action_template extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        if ($this->sql instanceof rex_sql) {
            $article_id = $this->sql->getValue('id');
            $template_id = $this->sql->getValue('template_id');
        } else {
            $article_id = 0;
            $template_id = 0;
        }

        // Plugin structure/content is not available
        if (!$this->structure_context->hasTemplates()) {
            return '';
        }

        // Plugin structure/content is available
        $template_select = new rex_template_select($this->structure_context->getCategoryId(), $this->structure_context->getClangId());
        $template_select->setName('template_id');
        $template_select->setSize(1);
        $template_select->setStyle('class="form-control selectpicker"');

        $template_name = $template_select->getTemplates();
        $template_name[0] = rex_i18n::msg('template_default_name');

        if ($this->structure_context->hasCategoryPermission()) {
            // Add article
            if ('add_art' == $this->structure_context->getFunction() && 0 == $article_id) {
                $template_select->setSelected();
                return $template_select->get();
            }

            // Edit article
            if ('edit_art' == $this->structure_context->getFunction() && $article_id == $this->structure_context->getArticleId()) {
                $template_select->setSelected($template_id);
                return $template_select->get();
            }
        }

        // Any other case
        return isset($template_name[$template_id]) ? $template_name[$template_id] : '';
    }
}
