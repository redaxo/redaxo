<?php
/**
 * Base class for all page fields
 */

abstract class rex_structure_field_base
{
    use rex_factory_trait;

    public const HEADER = 0;
    public const BODY = 1;
    public const FOOTER = 2;

    /**
     * @var string
     */
    protected $id;
    /**
     * @var bool
     */
    protected $type = 1;
    /**
     * @var rex_structure_context
     */
    protected $context;
    /**
     * @var rex_sql|null
     */
    protected $sql;
    /**
     * @var mixed
     */
    protected $condition;

    /**
     * @param string $id
     *
     * @return static
     */
    public static function factory($id)
    {
        $class = static::getFactoryClass();

        return new $class($id);
    }

    /**
     * @param string $id
     */
    protected function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param rex_structure_context $context
     * @param rex_sql|null $sql
     *
     * @return $this
     */
    public function setContext(?rex_structure_context $context, ?rex_sql $sql = null)
    {
        $this->context = $context;
        $this->sql = $sql;

        return $this;
    }

    /**
     * @param $condition
     *
     * @return $this
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCondition()
    {
        if (!isset($this->condition)) {
            return true;
        }

        if (is_callable($this->condition)){
            return call_user_func($this->condition, $this->context, $this->sql);
        }

        return $this->condition;
    }

    /**
     * @return string
     */
    abstract public function get();
}
