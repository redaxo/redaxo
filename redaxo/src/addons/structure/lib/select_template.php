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
     * @var array
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
    public function __construct($category_id = 0, $clang_id = 1)
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
     * @param int $selected
     */
    public function setSelected($selected = -1)
    {
        if ($selected > 0) {
            parent::setSelected($selected);
        } else {
            // Inherit template_id from start article
            if ($this->category_id > 0) {
                $sql = rex_sql::factory();
                $sql->setQuery('
                    SELECT template_id 
                    FROM '.rex::getTable('article').' 
                    WHERE id = '.$this->category_id.' 
                    AND clang_id = '.$this->clang_id.' 
                    AND startarticle = 1
                ');
                if ($sql->getRows() == 1) {
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
    }

    /**
     * @return array
     */
    protected function getTemplates()
    {
        if (!isset($this->templates)) {
            $this->templates = [];

            $templates = rex_template::getTemplatesForCategory($this->category_id);

            if (count($templates) > 0) {
                foreach ($templates as $t_id => $t_name) {
                    $this->templates[$t_id] = rex_i18n::translate($t_name, false);
                }
            }

            $this->templates[0] = rex_i18n::msg('template_default_name');
        }

        return $this->templates;
    }
}
