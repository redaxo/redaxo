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
     * @var rex_pager
     */
    protected $pager;
    /**
     * @var rex_sql
     */
    protected $sql;

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
     * @param $pager
     * @return $this
     */
    public function setPager(rex_pager $pager)
    {
        $this->pager = $pager;

        return $this;
    }

    /**
     * @param $sql
     * @return $this
     */
    public function setSql(rex_sql $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * This method returns html code for a link to call a rex_api_function
     * @return string
     */
    abstract public function get();

    /**
     * This method returns an optional modal window
     * @return string
     */
    public function getModal()
    {
        return '';
    }
}
