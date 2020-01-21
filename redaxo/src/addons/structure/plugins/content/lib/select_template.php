<?php
/**
 * @package redaxo\structure\content
 */
class rex_template_select extends rex_select
{
    /**
     * @var bool
     */
    private $loaded = false;
    /**
     * @var int
     */
    private $categoryId;
    /**
     * @var string[]
     */
    private $templates;
    /**
     * @var int
     */
    private $clangId;

    /**
     * @param int $categoryId
     * @param int $clangId
     */
    public function __construct($categoryId, $clangId)
    {
        $this->categoryId = (int) $categoryId;
        $this->clangId = (int) $clangId;

        parent::__construct();
    }

    /**
     * @return string
     */
    public function get()
    {
        if (!$this->loaded) {
            $templates = $this->getTemplates();

            if (count($templates) > 0) {
                foreach ($templates as $templateId => $templateName) {
                    $this->addOption($templateName, $templateId);
                }
            } else {
                $this->addOption(rex_i18n::msg('option_no_template'), '0');
            }

            $this->loaded = true;
        }

        return parent::get();
    }

    /**
     * @throws rex_sql_exception
     */
    public function setSelectedFromStartArticle()
    {
        $selected = null;

        // Inherit template_id from start article
        if ($this->categoryId > 0) {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT template_id FROM '.rex::getTable('article').' WHERE id = ? AND clang_id = ? AND startarticle = 1', [
                $this->categoryId,
                $this->clangId,
            ]);
            if (1 == $sql->getRows()) {
                $selected = $sql->getValue('template_id');
            }
        }

        $templates = $this->getTemplates();
        if (!$selected || !isset($templates[$selected])) {
            $selected = rex_template::getDefaultId();
        }

        if ($selected && isset($templates[$selected])) {
            parent::setSelected($selected);
        }
    }

    /**
     * @return string[]
     */
    public function getTemplates()
    {
        if (!isset($this->templates)) {
            $this->templates = [];

            $templates = rex_template::getTemplatesForCategory($this->categoryId);

            if (count($templates) > 0) {
                foreach ($templates as $templateId => $templateName) {
                    $this->templates[$templateId] = rex_i18n::translate($templateName, false);
                }
            }
        }

        return $this->templates;
    }
}
