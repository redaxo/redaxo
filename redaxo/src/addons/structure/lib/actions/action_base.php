<?php
abstract class rex_structure_action_base extends rex_structure_field_base
{
    use rex_factory_trait;

    /**
     * @var rex_structure_context
     */
    protected $structure_context;
    /**
     * @var rex_sql|null
     */
    protected $sql;

    /**
     * @param rex_structure_context $structure_context
     * @param rex_sql|null $sql
     *
     * @return $this
     */
    public function setContext(rex_structure_context $structure_context, $sql)
    {
        $this->structure_context = $structure_context;
        $this->sql = $sql;

        return $this;
    }

    abstract public function get();
}
