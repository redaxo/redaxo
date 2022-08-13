<?php

/**
 * @package redaxo\core\form
 */
abstract class rex_form_options_element extends rex_form_element
{
    /** @var array<string, string|int> */
    private $options;

    // 1. Parameter nicht genutzt, muss aber hier stehen,
    // wg einheitlicher Konstrukturparameter
    /**
     * @param string $tag
     * @param array<string, int|string> $attributes
     */
    public function __construct($tag = '', rex_form_base $form = null, array $attributes = [])
    {
        parent::__construct($tag, $form, $attributes);
        $this->options = [];
    }

    /**
     * @param string     $name
     * @param string|int $value
     * @return void
     */
    public function addOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * @param array<string|array{0: string, 1?: string|int}> $options
     * @param bool                                           $useOnlyValues
     * @return void
     */
    public function addOptions(array $options, $useOnlyValues = false)
    {
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

    /**
     * @param string[] $options
     * @param bool     $useKeys
     * @return void
     */
    public function addArrayOptions(array $options, $useKeys = true)
    {
        foreach ($options as $key => $value) {
            if (!$useKeys) {
                $key = $value;
            }

            $this->addOption($value, $key);
        }
    }

    /**
     * @param string $query
     * @return void
     */
    public function addSqlOptions($query)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getArray($query, [], PDO::FETCH_NUM));
    }

    /**
     * @param string $query
     * @return void
     */
    public function addDBSqlOptions($query)
    {
        $sql = rex_sql::factory();
        $this->addOptions($sql->getDBArray($query, [], PDO::FETCH_NUM));
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
