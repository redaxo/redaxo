<?php
class rex_structure_action_template extends rex_structure_action_base
{
    /**
     * @return string
     */
    public function get()
    {
        $tmpl_td = '';

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
            if ('add_art' == $this->structure_context->getFunction() && $this->sql->getValue('id') == 0) {
                $template_select->setSelected();
                return $template_select->get();
            }

            // Edit article
            if ('edit_art' == $this->structure_context->getFunction() && $this->sql->getValue('id') == $this->structure_context->getArticleId()) {
                $template_select->setSelected($this->sql->getValue('template_id'));
                return $template_select->get();
            }
        }

        // Any other case
        return isset($template_name[$this->sql->getValue('template_id')]) ? $template_name[$this->sql->getValue('template_id')] : '';
    }
}
