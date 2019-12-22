<?php

/**
 * @package redaxo\core\form
 */
abstract class rex_form_options_element extends rex_form_element
{
    /** @var array */
    private $options;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    public function __construct($tag = '', rex_form_base $table = null, array $attributes = [])
    {
        parent::__construct($tag, $table, $attributes);
        $this->options = [];
    }

    /**
     * @return void
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @return void
     */
    public function addOptions(array $options, $useOnlyValues = false)
    {
        if (count($options) > 0) {
            foreach ($options as $key => $option) {
                $option = (array) $option;
                if ($useOnlyValues) {
                    $this->addOption($option[0], $option[0]);
                } else {
                    if (!isset($option[1])) {
                        $option[1] = $key;
                    }

                    $this->addOption($option[0], $option[1]);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function addArrayOptions(array $options, $use_keys = true)
    {
        foreach ($options as $key => $value) {
            if (!$use_keys) {
                $key = $value;
            }

            $this->addOption($value, $key);
        }
    }

    /**
     * @return void
     */
    public function addSqlOptions($qry)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getArray($qry, [], PDO::FETCH_NUM));
    }

    /**
     * @return void
     */
    public function addDBSqlOptions($qry)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getDBArray($qry, [], PDO::FETCH_NUM));
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
