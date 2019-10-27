<?php
/**
 * @package redaxo\structure
 */
class rex_template_select extends rex_select
{
    /**
     * @var bool
     */
    protected $loaded = false;
    /**
     * @var int
     */
    protected $category_id;
    /**
     * @var string[]
     */
    protected $templates;
    /**
     * @var int
     */
    protected $clang_id;

    /**
     * @param int $category_id
     * @param int $clang_id
     */
    public function __construct($category_id, $clang_id)
    {
        $this->category_id = (int) $category_id;
        $this->clang_id = (int) $clang_id;

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
                foreach ($templates as $t_id => $t_name) {
                    $this->addOption($t_name, $t_id);
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
        // Inherit template_id from start article
        if ($this->category_id > 0) {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT template_id FROM '.rex::getTable('article').' WHERE id = ? AND clang_id = ? AND startarticle = 1', [
                $this->category_id,
                $this->clang_id,
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

            $templates = rex_template::getTemplatesForCategory($this->category_id);

            if (count($templates) > 0) {
                foreach ($templates as $t_id => $t_name) {
                    $this->templates[$t_id] = rex_i18n::translate($t_name, false);
                }
            }
        }

        return $this->templates;
    }
}
