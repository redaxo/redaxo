<?php
abstract class rex_structure_action_base
{
    use rex_factory_trait;

    /**
     * @var rex_structure_context
     */
    protected $structure_context;
    /**
     * @var rex_sql
     */
    protected $sql;

    /**
     * @param rex_structure_context $structure_context
     * @param rex_sql $sql
     *
     * @return mixed
     */
    public static function factory(rex_structure_context $structure_context, rex_sql $sql)
    {
        $class = static::getFactoryClass();

        return new $class($structure_context, $sql);
    }

    /**
     * @param rex_structure_context $structure_context
     * @param rex_sql $sql
     */
    protected function __construct(rex_structure_context $structure_context, rex_sql $sql)
    {
        $this->structure_context = $structure_context;
        $this->sql = $sql;
    }

    /**
     * This method implements the generation and return of an html action
     *
     * @return string
     */
    abstract public function get();
}
