<?php

/**
 * Template Objekt.
 * Zuständig für die Verarbeitung eines Templates.
 *
 * @package redaxo\structure\content
 */
class rex_template
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string|null
     */
    private $key;

    public function __construct($template_id)
    {
        $this->id = (int) $template_id;
        $this->key = '';
    }

    public static function getDefaultId()
    {
        return rex_config::get('structure/content', 'default_template_id', 1);
    }

    public static function forKey(string $template_key): ?self
    {
        $sql = rex_sql::factory()->setQuery(
            'SELECT `id` FROM '.rex::getTable('template').' WHERE `key` = :key',
            ['key' => $template_key]
        );

        if (1 == $sql->getRows()) {
            $template_id = $sql->getValue('id');

            $template = new self($template_id);
            $template->key == $template_key;
            return $template;
        }

        return null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getKey(): ?string
    {
        // key will never be empty string in the db
        if ('' === $this->key) {
            $sql = rex_sql::factory()->setQuery(
                'SELECT `key` FROM '.rex::getTable('template').' WHERE `id` = :id',
                ['id' => $this->id]
            );

            if (1 == $sql->getRows()) {
                $this->key = $sql->getValue('key');
            } else {
                $this->key = null;
            }
            assert('' !== $this->key);
        }

        return $this->key;
    }

    /**
     * @return false|string
     */
    public function getFile()
    {
        if ($this->getId() < 1) {
            return false;
        }

        $file = $this->getFilePath($this->getId());
        if (!$file) {
            return false;
        }

        if (!file_exists($file)) {
            // Generated Datei erzeugen
            if (!$this->generate()) {
                throw new rex_exception('Unable to generate rexTemplate with id "' . $this->getId() . '"');
            }
        }

        return $file;
    }

    /**
     * @return false|string
     */
    public static function getFilePath($template_id)
    {
        if ($template_id < 1) {
            return false;
        }

        return self::getTemplatesDir() . '/' . $template_id . '.template';
    }

    /**
     * @return string
     */
    public static function getTemplatesDir()
    {
        return rex_path::addonCache('templates');
    }

    public function getTemplate()
    {
        $file = $this->getFile();
        if (!$file) {
            return false;
        }

        return rex_file::get($file);
    }

    /**
     * @return bool
     */
    public function generate()
    {
        $template_id = $this->getId();

        if ($template_id < 1) {
            return false;
        }

        $sql = rex_sql::factory();
        $qry = 'SELECT * FROM ' . rex::getTablePrefix()  . 'template WHERE id = ' . $template_id;
        $sql->setQuery($qry);

        if (1 == $sql->getRows()) {
            $templateFile = self::getFilePath($template_id);

            $content = $sql->getValue('content');
            $content = rex_var::parse($content, rex_var::ENV_FRONTEND, 'template');
            if (false !== rex_file::put($templateFile, $content)) {
                return true;
            }
            throw new rex_exception('Unable to generate template ' . $template_id . '!');
        }
        throw new rex_exception('Template with id "' . $template_id . '" does not exist!');
    }

    /**
     * @return bool
     */
    public function deleteCache()
    {
        if ($this->id < 1) {
            return false;
        }

        $file = $this->getFilePath($this->getId());
        rex_file::delete($file);
        return true;
    }

    /**
     * Returns an array containing all templates which are available for the given category_id.
     * if the category_id is non-positive all templates in the system are returned.
     * if the category_id is invalid an empty array is returned.
     *
     * @param int  $category_id
     * @param bool $ignore_inactive
     *
     * @return array
     */
    public static function getTemplatesForCategory($category_id, $ignore_inactive = true)
    {
        $templates = [];
        $t_sql = rex_sql::factory();
        $where = $ignore_inactive ? ' WHERE active=1' : '';
        $t_sql->setQuery('select id,name,attributes from ' . rex::getTablePrefix() . 'template' . $where . ' order by name');

        if ($category_id < 1) {
            // Alle globalen Templates
            foreach ($t_sql as $row) {
                $attributes = $row->getArrayValue('attributes');
                $categories = $attributes['categories'] ?? [];
                if (!is_array($categories) || (isset($categories['all']) && 1 == $categories['all'])) {
                    $templates[$row->getValue('id')] = $row->getValue('name');
                }
            }
        } else {
            if ($c = rex_category::get($category_id)) {
                $path = $c->getPathAsArray();
                $path[] = $category_id;
                foreach ($t_sql as $row) {
                    $attributes = $row->getArrayValue('attributes');
                    $categories = $attributes['categories'] ?? [];
                    // template ist nicht kategoriespezifisch -> includen
                    if (!is_array($categories) || (isset($categories['all']) && 1 == $categories['all'])) {
                        $templates[$row->getValue('id')] = $row->getValue('name');
                    } else {
                        // template ist auf kategorien beschraenkt..
                        // nachschauen ob eine davon im pfad der aktuellen kategorie liegt
                        foreach ($path as $p) {
                            if (in_array($p, $categories)) {
                                $templates[$row->getValue('id')] = $row->getValue('name');
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $templates;
    }

    /**
     * @return bool
     */
    public static function hasModule(array $template_attributes, $ctype, $module_id)
    {
        $template_modules = $template_attributes['modules'] ?? [];
        if (!isset($template_modules[$ctype]['all']) || 1 == $template_modules[$ctype]['all']) {
            return true;
        }

        if (is_array($template_modules[$ctype]) && in_array($module_id, $template_modules[$ctype])) {
            return true;
        }

        return false;
    }
}
