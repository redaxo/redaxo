<?php
/**
 * @package redaxo/structure/content
 * @var rex_sql $article
 */

abstract class rex_structure_button
{
    /**
     * @var rex_context
     */
    protected $context;
    /**
     * @var array
     */
    protected $params;
    /**
     * @var int
     */
    protected $edit_id;

    /**
     * @param int $id
     * @param rex_context $context
     * @param array $params
     * @return static
     */
    public static function init($id, rex_context $context, array $params = [])
    {
        return new static($id, $context, $params);
    }

    /**
     * @param int $id
     * @param rex_context $context
     * @param array $params
     */
    public function __construct($id, rex_context $context, array $params = [])
    {
        $this->edit_id = $id;
        $this->context = $context;
        $this->params = $params;
    }

    /**
     * This method returns html code for a link to call a rex_api_function
     * @return string
     */
    abstract public function get();
}
